UserProfileV2 is a MediaWiki extension for displaying User Profiles in MediaWiki. It was built to replace SocialProfile,
which is a big mess, hence the name "V2" — could've just called it SocialProfileV2?

The extension is intended to work on WikiFarms, such as Telepedia and Miraheze, but will work on a stand-alone wiki. It
also integrates with CentralAuth to fetch global edit counts and global user rights. Again, it will work without
CentralAuth and will just fetch local groups.

# Configuration

Firstly load the extension using `wfLoadExtension( 'UserProfileV2' )`. There are some configuration options to make your
life easier:

* `wgUserProfileV2Color`: The secondary colour to use for the background colour on things like group tags and buttons.
* `wgUserProfileV2AvatarBorderRadius`: The border radius of the avatar.
* `wgUserProfileV2Backend`: The backend to use for avatars (see below).

## Backend

UserProfileV2 will use the backend defined in `$wgUserProfileV2Backend` if it is set, which allows you to use a backend
such as S3 or Swift. This must correspond to a backend registered with `$wgFileBackend` such as

```php
$wgFileBackends[] = [
	'class'              => SwiftFileBackend::class,
	'name'               => 'userprofilev2',
	'wikiId'             => $wgDBname,
	// more configuration here
];
```

Theoretically it is possible to have global avatars (instead of each wiki using its own avatars) if you
set `$wgFileBackends['userprofilev2']['wikiId]` to a central wiki instead of dynamically setting it via `$wgDBname`.
This has not been tested yet.

If you do not provide `$wgUserProfileV2Backend` then the extension will construct a `new FSFileBackend([])` with
configuration for individual wiki avatars.

# Security Vulnerabilities

If you believe you have found a security vulnerability in any part of our code, please do not post it publicly by using
our wikis or bug trackers for that.

As a quick overview, please file a
task [here](https://telepedia.atlassian.net/servicedesk/customer/portal/1/group/1/create/4).
