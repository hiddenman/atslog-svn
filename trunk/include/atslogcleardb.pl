#!/usr/bin/perl
# ATSlog version @version@ build @buildnumber@ www.atslog.dp.ua  
# Copyright (C) 2003 Denis CyxoB www.yamiyam.dp.ua
#                                                       
# ������ ������� ���������� ���� ����� �����
if ($ARGV[0] ne "yes"){
    exit 1;
}

use DBI;
use POSIX qw(locale_h); # ��� ���������� ��������� �������� ��������.

$config_file="/usr/local/etc/atslog.conf";

open(IN,$config_file) || die print("Can't open config file $config_file.");
while(<IN>) {
    next if /^#/;
    chomp;
    ($key,$val) = ( /([^=]+)=(.*)/ );
    $key = lc($key);
    $vars{$key} = $val;
}
close(IN);

$langfileprefix=$vars{sharedir}."/".$vars{langdir}."/";
$langfile=$langfileprefix.setlocale(LC_CTYPE);

if ( ! -f $langfile){
    $langfile=$langfileprefix."en_US";
}

open(LN,$langfile) || die print("Can't open language file $langfile.");
while(<LN>) {
    next if /^#/;
    chomp;
    ($key,$val) = ( /([^=]+)=\"(.*)\"/ );
    $key = lc($key);
    $vars{$key} = $val;
}
close(LN);

print $vars{msg29}."calls: ";

if($vars{sqltype} =~ /PostgreSQL/i){
    $sqltype=Pg;
}else{
    $sqltype=mysql;
}

$host="";
if($vars{sqlhost} ne "localhost"){
    $host = ";host=".$vars{sqlhost};
}
	
$dbh = DBI->connect("dbi:$sqltype:dbname=$vars{sqldatabase}$host",$vars{sqlmasteruser},$vars{sqlmaspasswd},{PrintError=>0});

$del_query="TRUNCATE TABLE calls;";
#print $del_query;

$sth = $dbh->prepare($del_query);
if ($sth->execute) {
    print $vars{msg17}."\n";
    $toexit=0;
}else{
    print $vars{msg30}."\n";
    $toexit=1;
}
$dbh->disconnect;
exit $toexit;
