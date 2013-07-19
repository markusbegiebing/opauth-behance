Opauth-Behance
=============
[Opauth][1] strategy for Behance authentication.

Opauth is a multi-provider authentication framework for PHP.

Getting started
----------------
1. Install Opauth-Behance:
   ```bash
   cd path_to_opauth/Strategy
   git clone git://github.com/markusbegiebing/opauth-behance.git Behance
   ```

2. Create behance application at http://www.behance.net/dev/register
   - Make sure to enter a Redirect URI (for OAuth).
   - Make sure that Redirect URI is set to actual OAuth 2.0 callback URL, usually `http://path_to_opauth/behance/oauth2callback`
   
3. Configure Opauth-Behance strategy.

4. Direct user to `http://path_to_opauth/behance to authenticate


Strategy configuration
----------------------

Required parameters:

```php
<?php
'Behance' => array(
	'client_id' => 'YOUR CLIENT ID',
	'client_secret' => 'YOUR CLIENT SECRET'
)
```

Optional parameters:
`scope`, `state`, `access_type`, `approval_prompt`

[1]: https://github.com/uzyn/opauth