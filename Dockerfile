FROM --platform=linux/amd64 php:8.4.26-apache@sha256:60111400ff77bdc44fa79b4f072afe49a42abba71e0846ad496be4761dc9c11a

# hardening theater: hide engine banner
RUN { \
  echo 'ServerTokens Prod'; \
  echo 'ServerSignature Off'; \
  echo 'Header always unset X-Powered-By'; \
} > /etc/apache2/conf-enabled/zz-harden.conf \
 && a2enmod headers rewrite

# "hardened" php settings.
# memory_limit=8M: low per-request cap is the realistic shared-hosting posture AND
# the precondition for the OOM-corruption path to be reachable (Rook r2/r3 finding:
# a 128M limit makes the engine-level chain effectively unexploitable).
RUN { \
  echo 'expose_php = Off'; \
  echo 'session.upload_progress.enabled = 1'; \
  echo 'memory_limit = 8M'; \
  echo 'post_max_size = 64M'; \
  echo 'upload_max_filesize = 32M'; \
  echo 'session.gc_probability = 0'; \
} > /usr/local/etc/php/conf.d/harden.ini

# prefork: bounded worker lifetime so heap state is reproducible across attempts
RUN { \
  echo 'MaxRequestsPerChild 200'; \
  echo 'MaxConnectionsPerChild 200'; \
} >> /etc/apache2/conf-enabled/zz-harden.conf

# suid audit helper = the privilege-escalation path to proof.txt
# (chain-closure fix: without this there is no route from www-data to a 0600 root file)
COPY vault-check.c /tmp/vault-check.c
RUN gcc -O1 -o /usr/local/bin/vault-check /tmp/vault-check.c \
 && chown root:root /usr/local/bin/vault-check && chmod 4755 /usr/local/bin/vault-check \
 && mkdir -p /var/vault-backup && chown www-data:www-data /var/vault-backup \
 && echo "integrity baseline OK" > /var/vault-backup/README.bak \
 && rm /tmp/vault-check.c

COPY app/ /var/www/html/
COPY private.ini /var/www/private/credentials.ini
RUN mkdir -p /var/www/uploads && chown -R www-data:www-data /var/www/uploads \
 && chown root:www-data /var/www/private/credentials.ini && chmod 640 /var/www/private/credentials.ini

# flags (OverTheWire style) — override at build: --build-arg FLAG_LOCAL=... --build-arg FLAG_PROOF=...
ARG FLAG_LOCAL=flag{replace-at-build}
ARG FLAG_PROOF=flag{replace-at-build}
RUN echo "$FLAG_LOCAL"   > /var/www/local.txt \
 && chmod 640 /var/www/local.txt && chown root:www-data /var/www/local.txt \
 && echo "$FLAG_PROOF"  > /root/proof.txt \
 && chmod 600 /root/proof.txt

EXPOSE 80
WORKDIR /var/www/html

# v4: boot-time session seeder keeps the leak window deterministically open
COPY seed-entrypoint.sh /usr/local/bin/seed-entrypoint.sh
RUN chmod +x /usr/local/bin/seed-entrypoint.sh
ENTRYPOINT ["/usr/local/bin/seed-entrypoint.sh"]
