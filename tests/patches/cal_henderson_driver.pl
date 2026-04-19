#!/usr/bin/env perl
# Driver for Cal Henderson's 2003 Perl Homespring module.
# Usage: cal_henderson_driver.pl <program.hs>
# Env: HS_LIMIT=N (default 100), HS_TICKS=1 to report tick count on stderr.
# Note: the Perl module is incomplete and does not support stdin/input nodes.

use strict;
use warnings;
use FindBin qw($Bin);
use lib "$Bin/../../interpreters/2003-cal-henderson";
use lib "$Bin/../../interpreters/2003-cal-henderson/blib/lib";
use Language::Homespring;

my $file = shift @ARGV or die "usage: $0 <program.hs>\n";
my $limit = $ENV{HS_LIMIT} || 100;
my $report_ticks = ($ENV{HS_TICKS} || '') eq '1';

open my $fh, '<', $file or die "cannot open $file: $!";
local $/; my $src = <$fh>; close $fh;

my $hs = Language::Homespring->new();
$hs->parse($src);

my $ticks = 0;
for (my $i = 0; $i < $limit; $i++) {
    my $out = $hs->tick();
    $ticks++;
    last if !$hs->{universe_ok};
    print $out if defined $out;
}

print STDERR "TICKS:$ticks\n" if $report_ticks;
