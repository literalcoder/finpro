
{ pkgs }: {
  deps = [
    pkgs.mariadb
    pkgs.php82
    pkgs.php82Packages.pdo_mysql
  ];
}
