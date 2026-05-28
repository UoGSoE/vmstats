# VMStats
This is a basic Laravel app to record which VM servers and guests are kicking about.

## Web front end
You should be able to log in at https://your-vmstats.example.com/login.  If it's a brand new install you will need to create a user beforehand though (after that you can manage other users through the UI) :
```sh
php artisan tinker
```
Assuming you are using LDAP then
```php
$user = \App\Models\User::create(['username' => 'your-username', 'email' => 'you@example.com', 'surname' => 'Smith', 'forenames' => 'Jenny', 'is_staff' => true, 'password' => bcrypt(\Str::random(64))]);
```
Otherwise set the password to be `bcrypt('your-amazing-password')`.

## API
It's mostly designed to be used via it's HTTP API using curl or the like.

### Authentication
The API uses Laravel Sanctum bearer tokens.

- `GET /api/servers` **always** requires a valid token.
- The mutating routes (`POST /api/vms`, `POST /api/vms/delete`, `POST /api/servers/delete`, `POST /api/server/notes`, `POST /api/guest/notes`) require a token **when** the `VMSTATS_API_AUTH_REQUIRED` configuration variable is `true`. Default is `false` so existing field scripts keep working until you're ready to flip the switch.

Pass the token as a bearer header on every call:

```sh
curl -H "Authorization: Bearer ${VMSTATS_TOKEN}" ...
```

### Issuing and managing tokens
Any logged-in user can manage tokens via the **API Keys** page (linked from the top nav). For each token:

- Pick the user it should belong to (defaults to you).
- Give it a meaningful name (e.g. `kvm-host-42`).
- The plain-text token is shown **once** on creation — copy it before dismissing the dialog. After that only the hash is stored.

To revoke a token, use the trash icon on its row. The revoke dialog offers two paths:

- **Transfer** to another user. The plain-text token value is preserved, so any script using it keeps working — useful when a colleague leaves the team and you don't want to walk round 100 servers reconfiguring `/etc/vmstats.conf`.
- **Delete permanently**. The token is revoked outright; any script using it will start getting 401s.

Deleting a user who owns tokens (via the User Management page) offers the same transfer-or-destroy choice.

### Deploying a token to a server
Either set `VMSTATS_TOKEN` in the script's environment, or put the value in `/etc/vmstats.conf` and source it:

```sh
echo 'VMSTATS_TOKEN=your-token-here' > /etc/vmstats.conf
chmod 600 /etc/vmstats.conf
```

See `kvm_vmstats.sh` in the base of the repository for a working example.

### Endpoints

```sh
# record a vm running on a server
curl -X POST -H "Authorization: Bearer ${VMSTATS_TOKEN}" \
    -d 'server=server.example.com' -d 'guest=vm.example.com' \
    https://vmstats.example.com/api/vms
# record a vm running on a server and add some notes
curl -X POST -H "Authorization: Bearer ${VMSTATS_TOKEN}" \
    -d 'server=server.example.com' -d 'guest=vm.example.com' \
    -d 'guest_notes=I am a VM' -d 'server_notes=I am a server' \
    https://vmstats.example.com/api/vms
# record a vm running on a server and add some base64 encoded notes
curl -X POST -H "Authorization: Bearer ${VMSTATS_TOKEN}" \
    -d 'server=server.example.com' -d 'guest=vm.example.com' \
    -d 'guest_notes_b64=aSBhbSBhIFZNCg==' -d 'server_notes_b64=aSBhbSBhIHNlcnZlcgo=' \
    https://vmstats.example.com/api/vms
# update just the notes for a server or vm
curl -X POST -H "Authorization: Bearer ${VMSTATS_TOKEN}" \
    -d 'name=server.example.com' -d 'notes=I am a server' \
    https://vmstats.example.com/api/server/notes
curl -X POST -H "Authorization: Bearer ${VMSTATS_TOKEN}" \
    -d 'name=vm.example.com' -d 'notes=I am a vm' \
    https://vmstats.example.com/api/guest/notes
# delete a server or vm
curl -X POST -H "Authorization: Bearer ${VMSTATS_TOKEN}" \
    -d 'name=server.example.com' https://vmstats.example.com/api/servers/delete
curl -X POST -H "Authorization: Bearer ${VMSTATS_TOKEN}" \
    -d 'name=vm.example.com' https://vmstats.example.com/api/vms/delete
# get a list of all the servers and their guests
curl -H "Authorization: Bearer ${VMSTATS_TOKEN}" https://vmstats.example.com/api/servers
```

### Example script
There is an example script for using this to record Linux KVM guests in `kvm_vmstats.sh` in the base of the repository.
