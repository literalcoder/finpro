
{ pkgs }: {
  deps = [
    pkgs.mysql84
    pkgs.php82
    pkgs.php82Packages.pdo_mysql
  ];
}
