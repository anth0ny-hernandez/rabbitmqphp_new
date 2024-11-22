#!/bin/bash

#Create an archive filder, show the zipping process, zip up the file, specify filename of archive to be created
latestVersionNum=$(tail -n 1 "versionTracker.txt")
newVersionNum=$(($latestVersionNum + 1))
echo $newVersionNum
tar -cvf rabbitmqphp_new.${newVersionNum}.tar testdir
#create scp command to send .tar file over to deployment server
