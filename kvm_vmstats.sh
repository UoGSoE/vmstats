#!/bin/bash
#
# pings the vmstats service with currently running guests
#


for H in `virsh list | tail -n +3 | grep 'running' | awk '{print $2}' | egrep -v '^$'`;
do
    VMINFO=`virsh dominfo ${H} | base64`
	curl -s -X POST -d "server=${HOSTNAME}" -d "guest=${H}" -d "guest_notes_b64=${VMINFO}"  "https://vmstats.example.com/api/vms"
done

SRVINFO=`virsh nodeinfo | base64`
HOST=`hostname`
curl -s -X POST -d "name=${HOST}" -d "notes_b64=${SRVINFO}"  "https://vmstats.example.com/api/server/notes"
