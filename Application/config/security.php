<?php
return [                
    
    'services'=>[
        'user_repository' => [
            'class' => 'Classes\\UserRepository'
        ],

        'authenticator'   => [
            'class'  => 'Services\\Security\\Authentication\\DbAuthenticator',
            'params' => ['@user_repository']
        ],
                
    ]
    
    
    
    /*'authenticator'   => [
        'class'  => 'Services\\Security\\Authentication\\LdapAuthenticator',
        'params' => ['@user_repository', '@ldap']
    ],
        
    ]*/
];