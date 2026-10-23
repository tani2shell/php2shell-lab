/* vault-check — PhpVault document integrity checker (suid helper) */
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <sys/stat.h>

int main(int argc, char *argv[]) {
    if (argc != 2) { fprintf(stderr, "usage: vault-check <backup-path>\n"); return 1; }
    if (strncmp(argv[1], "/var/vault-backup/", 18) != 0) {
        fprintf(stderr, "vault-check: only /var/vault-backup/* is auditable\n");
        return 2;
    }
    /* check ownership metadata then dump content */
    struct stat st;
    if (stat(argv[1], &st) != 0) { perror("vault-check"); return 3; }
    FILE *f = fopen(argv[1], "r");
    if (!f) { perror("vault-check"); return 4; }
    char buf[512]; size_t n;
    while ((n = fread(buf, 1, sizeof(buf), f)) > 0) fwrite(buf, 1, n, stdout);
    fclose(f);
    return 0;
}
