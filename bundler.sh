#!/bin/bash

#Create an archive filder, show the zipping process, zip up the file, specify filename of archive to be created

#find the latest version number tracked and add 1 to it
latestVersionNum=$(tail -n 1 "../rabbitmqphp_new/versionTracker.txt")

#source reference for converting string to num: https://linuxhandbook.com/bash-convert-string-to-number/
newVersionNum=$(($latestVersionNum + 1))
echo $newVersionNum

#append that new version number to tracker
echo $newVersionNum >> "../rabbitmqphp_new/versionTracker.txt"

#source reference for tarring: https://stackoverflow.com/questions/50338201/how-to-compress-and-tar-a-folder-in-linux
tar -czf rabbitmqphp_new.${newVersionNum}.tar.gz $*

#create scp command to send .tar file over to deployment server
scp rabbitmqphp_new.${newVersionNum}.tar.gz yashmandal@172.22.217.86:/home/yashmandal/git/deployment
#scp rabbitmqphp_new.${newVersionNum}.tar.gz alvee-jalal@172.22.87.142:/home/alvee-jalal/git/