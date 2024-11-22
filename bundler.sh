#!/bin/bash

#Create an archive filder, show the zipping process, zip up the file, specify filename of archive to be created
tar -cvf rabbitmqphp_new.tar $*
#create scp command to send .tar file over to deployment server
