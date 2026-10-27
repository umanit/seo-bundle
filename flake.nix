{
  description = "seo-bundle dev shell (PHP 8.4)";

  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-26.05";
    php-dev-shell = {
      url = "github:DjLeChuck/php-dev-shell";
      inputs.nixpkgs.follows = "nixpkgs";
    };
  };

  outputs =
    { nixpkgs, php-dev-shell, ... }:
    let
      systems = [
        "x86_64-linux"
        "aarch64-linux"
        "x86_64-darwin"
        "aarch64-darwin"
      ];
      forEachSystem = f: nixpkgs.lib.genAttrs systems (system: f system);
    in
    {
      devShells = forEachSystem (
        system:
        let
          pkgs = import nixpkgs { inherit system; };
        in
        {
          default = php-dev-shell.lib.mkDevShell {
            inherit pkgs;
            php = pkgs.php84;

            # Extensions PHP utilisées par seo-bundle, en plus des extensions par
            # défaut de nixpkgs (openssl, pdo, session, sockets, ctype, fileinfo,
            # iconv, json, posix, xml, ...).
            extraPhpExtensions =
              all: with all; [
              ];
          };
        }
      );
    };
}
