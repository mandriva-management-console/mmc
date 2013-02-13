#!/usr/bin/perl
#
#  Real time squid log viewer (Visualização dos logs do squid em tempo real)
#  Antonio Lobato 
#  lobato@tinecon.com.br
#  02/Jul/2007        

#TODO (02/02/2008)
#
#  - Send results formated in JSON.
#  - applicate filters (setted on requision) for get last $num_lines records (user, url domain(url), maindomain(url), ip, denied, etc...().)
#  - Don`t relay read to Unix tail, but to open().

#use CGI::Pretty qw( :standard );
use strict;
use warnings;

#print header();
print "Content-Type: text/html; charset=UTF-8\n\n";

### set varialeds ####
my $num_lines = 18;
my $logfile = $ARGV[0] || "/var/log/squid/access.log";

### get log records ####
my @lines = qx(tail -$num_lines $logfile);

### reverse, process and print records ####
my $lastuserurl = '';
for (reverse @lines)
{
   # extract interesting fields
   my ($time, $ip, $disp, $url, $user) = (split /\s+/)[0,2,3,6,7];

   # ignore repetead user+url
   next if $lastuserurl eq "$user$url";

   # finally, send formated results
   print 
      sprintf("%02i:%02i:%02i", (localtime($time))[2,1,0] ). # h:m:s
      " $user $ip $url ".                                    # user ip url
      ( ($disp =~ /denied/i)?1:0 ).                          # 0 | 1  (telling javascript on browser if the access was denied)
      "\n";
   
   # remember user+url
   $lastuserurl = "$user$url";
}

























#   my $date = sprintf("%02i:%02i:%02i", (localtime($time))[2,1,0] );

#   print "$time, $ip, $disp, $url, $user\n";

#print "Content-type: text/plain
#
#";


#use CGI::Pretty qw(:all);
   #print "$date\t$ip\t". ( ($disp =~ /denied/i)?1:0 ) ."\t$url\t$user\n";
   #$ip = "$1" if ($ip =~ /192\.168\.0(\.\d{1,3})/);
   #print exists $lastuserurl{"$user$url"},"\t","$lastuserurl{'$user$url'}"."\n";


#my $formuser;
#if (defined param('formuser') and param('formuser') ne "")
#{
#	$formuser = param('formuser');
#} else {
#	$formuser = "";
#}
#
#my $formip;
#if (defined param('formip') and param('formip') ne "")
#{
#	$formip = param('formip');
#} else {
#	$formip = "";
#}
#
#my $formurl;
#if (defined param('formurl') and param('formurl') ne "")
#{
#	$formurl = param('formurl');
#} else {
#	$formurl = "";
#}
#
#my $interval;
#if (defined param('interval') and param('interval') ne "")
#{
#	$interval = param('interval');
#} else {
#	$interval = 3;
#}


