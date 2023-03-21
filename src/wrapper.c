#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>

int main(int argc, char *argv[]) {
    if (argc != 3) {
        fprintf(stderr, "Usage: %s accountName newPrimaryDomain\n", argv[0]);
        exit(1);
    }

    char *accountName = argv[1];
    char *newPrimaryDomain = argv[2];

    setuid(0);
    clearenv();

    char cmd[512];
    snprintf(cmd, sizeof(cmd), "/usr/local/cpanel/base/frontend/jupiter/primary/script.sh '%s' '%s'", accountName, newPrimaryDomain);

    system(cmd);

    return 0;
}