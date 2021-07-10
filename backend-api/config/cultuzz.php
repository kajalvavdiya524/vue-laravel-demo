<?php

return [

  'endpoint'          => env('CULT_ENDPOINT'),
  'endpoint_basic'    => env('CULT_ENDPOINT_BASIC'),
  'endpoint_collab'   => env('CULT_ENDPOINT_COLLAB'),
  'endpoint_mappings' => env('CULT_ENDPOINT_MAPPINGS'),

  'agent_sine'            => env('CULT_AGENT_SINE'),
  'agent_sine_collab'     => env('CULT_AGENT_SINE_COLLAB'),
  'agent_dutycode'        => env('CULT_AGENT_DUTYCODE'),
  'agent_dutycode_collab' => env('CULT_AGENT_DUTYCODE_COLLAB'),

  'register_token' => env('CULT_REGISTER_TOKEN'),
  'auth_token'     => env('CULT_AUTH_TOKEN'),

  'signature_key' => env('CULT_SIGNATURE_KEY'),

  'notify_email' => env('CULT_NOTIFY_EMAIL', null),

  'default_pull_channel'   => env('CULT_DEFAULT_PULL_CHANNEL', '55075'),
  'enabled_channels'       => explode(',', env('CULT_ENABLED_CHANNELS', '')),
  'push_channels_login'    => env('CULT_PUSH_CHANNELS_LOGIN'),
  'push_channels_password' => env('CULT_PUSH_CHANNELS_PASSWORD'),

  'cultdata_key' => env('CULT_DATA_API_KEY'),
];
