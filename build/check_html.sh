#!/bin/bash

grep -i '<input' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build | grep '/>' -v
grep -i '<img' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build | grep '/>' -v

grep -i ' maxlength=0' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' maxlength=1' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' maxlength=2' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' maxlength=3' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' maxlength=4' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' maxlength=5' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' maxlength=6' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' maxlength=7' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' maxlength=8' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' maxlength=9' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build

grep -i ' size=0' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' size=1' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' size=2' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' size=3' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' size=4' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' size=5' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' size=6' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' size=7' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' size=8' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' size=9' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build

grep -i ' colspan=0' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' colspan=1' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' colspan=2' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' colspan=3' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' colspan=4' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' colspan=5' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' colspan=6' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' colspan=7' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' colspan=8' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' colspan=9' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build

grep -i ' cellpadding=0' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' cellpadding=1' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' cellpadding=2' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' cellpadding=3' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' cellpadding=4' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' cellpadding=5' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' cellpadding=6' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' cellpadding=7' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' cellpadding=8' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' cellpadding=9' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build

grep -i ' cellspacing=0' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' cellspacing=1' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' cellspacing=2' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' cellspacing=3' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' cellspacing=4' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' cellspacing=5' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' cellspacing=6' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' cellspacing=7' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' cellspacing=8' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' cellspacing=9' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build

grep -i ' tabindex=0' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' tabindex=1' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' tabindex=2' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' tabindex=3' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' tabindex=4' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' tabindex=5' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' tabindex=6' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' tabindex=7' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' tabindex=8' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
grep -i ' tabindex=9' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build

grep -i 'type=submit' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build

grep -i 'type=hidden' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build

grep -i 'type=text' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build

grep -i 'type=checkbox' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build

grep -i 'type=radio' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build

grep -i 'type=file' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build

grep -i '<center>' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build

grep -i 'textbox' * -Rrn --exclude-dir=locale --exclude-dir=includes/phplot --exclude-dir=includes/tcpdf --exclude-dir=doc --exclude-dir=build
