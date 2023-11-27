# VMStats
This is a basic Laravel app to record which VM servers and guests are kicking about

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
It's mostly designed to be used via it's HTTP API using curl or the like.  Available endpoint calls are :
```sh
# record a vm running on a server
curl -X POST -d 'server=server.example.com' -d 'guest=vm.example.com' https://vmstats.example.com/api/vms
# record a vm running on a server and add some notes
curl -X POST -d 'server=server.example.com' -d 'guest=vm.example.com' -d 'guest_notes=I am a VM' -d 'server_notes=I am a server' https://vmstats.example.com/api/vms
# record a vm running on a server and add some base64 encoded notes
curl -X POST -d 'server=server.example.com' -d 'guest=vm.example.com' -d 'guest_notes_b64=aSBhbSBhIFZNCg==' -d 'server_notes_b64=aSBhbSBhIHNlcnZlcgo=' https://vmstats.example.com/api/vms
# update just the notes for a server or vm
curl -X POST -d 'name=server.example.com' -d 'notes=I am a server' https://vmstats.example.com/api/server/notes
curl -X POST -d 'name=vm.example.com' -d 'notes=I am a vm' https://vmstats.example.com/api/guest/notes
curl -X POST -d 'name=server.example.com' -d 'notes_b64=aSBhbSBhIHNlcnZlcgo=' https://vmstats.example.com/api/server/notes
curl -X POST -d 'name=vm.example.com' -d 'notes=aSBhbSBhIFZNCg==' https://vmstats.example.com/api/guest/notes
# delete a server or vm
curl -X POST -d 'name=server.example.com' https://vmstats.example.com/api/servers/delete
curl -X POST -d 'name=vm.example.com' https://vmstats.example.com/api/vms/delete
# get a list of all the servers and their guests
curl https://vmstats.example.com/api/servers
```

### Example
There is an example script for using this to record Linux KVM guests in `kvm_vmstats.sh` in the base of the repository.
