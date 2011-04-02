#!/bin/bash

grep "'SELECT " * -rRn --exclude-dir='build'
grep "'UPDATE " * -rRn --exclude-dir='build'
grep "'INSERT " * -rRn --exclude-dir='build'
grep "'DELETE " * -rRn --exclude-dir='build'

