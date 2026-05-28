#!/bin/bash
#
# pings the vmstats service with currently running guests
#

# The VMStats API requires a bearer token once VMSTATS_API_AUTH_REQUIRED is on.
# Provide it via the VMSTATS_TOKEN environment variable, or via a config file
# sourced below:
#
#   echo 'VMSTATS_TOKEN=your-token-here' > /etc/vmstats.conf
#   chmod 600 /etc/vmstats.conf
#
[ -f /etc/vmstats.conf ] && . /etc/vmstats.conf

AUTH_HEADER="Authorization: Bearer ${VMSTATS_TOKEN}"

for H in `virsh list | tail -n +3 | grep 'running' | awk '{print $2}' | egrep -v '^$'`;
do
    VMINFO=`virsh dominfo ${H} | base64`
	curl -s -X POST -H "${AUTH_HEADER}" -d "server=${HOSTNAME}" -d "guest=${H}" -d "guest_notes_b64=${VMINFO}"  "https://vmstats.example.com/api/vms"
done

SRVINFO=`virsh nodeinfo | base64`
HOST=`hostname`
curl -s -X POST -H "${AUTH_HEADER}" -d "name=${HOST}" -d "notes_b64=${SRVINFO}"  "https://vmstats.example.com/api/server/notes"
