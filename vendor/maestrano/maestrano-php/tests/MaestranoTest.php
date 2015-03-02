<?php

class MaestranoTest extends PHPUnit_Framework_TestCase
{
    protected $config;
  
    protected function setUp()
    {
      $this->config = array(
        'environment' => 'production',
        'app' => array(
          'host' => "https://mysuperapp.com",
        ),
        'api' => array(
          'id' => "myappid",
          'key' => "myappkey",
        ),
        'sso' => array(
          'init_path' => "/mno/init_path.php",
          'consume_path' => "/mno/consume_path.php",
          'idp' => "https://mysuperidp.com"
        ),
        'webhook' => array(
          'account' => array(
            'groups_path' => "/mno/groups/:id",
            'group_users_path' => "/mno/groups/:group_id/users/:id"
          ),
          'connec' => array(
            'notifications_path' => "/mno/connec/notifications",
            'subscriptions' => array(
              'organizations' => true,
              'people' => true
            )
          )
        )
      );
    }
    
    public function testBindingConfiguration() {
      Maestrano::configure($this->config);
      
      $this->assertEquals($this->config['environment'], Maestrano::param('environment'));
      $this->assertEquals($this->config['app']['host'], Maestrano::param('app.host'));
      $this->assertEquals($this->config['api']['id'], Maestrano::param('api.id'));
      $this->assertEquals($this->config['api']['key'], Maestrano::param('api.key'));
      $this->assertEquals($this->config['sso']['init_path'], Maestrano::param('sso.init_path'));
      $this->assertEquals($this->config['sso']['consume_path'], Maestrano::param('sso.consume_path'));
      $this->assertEquals($this->config['webhook']['account']['groups_path'], Maestrano::param('webhook.account.groups_path'));
      $this->assertEquals($this->config['webhook']['account']['group_users_path'], Maestrano::param('webhook.account.group_users_path'));
      $this->assertEquals($this->config['webhook']['connec']['notifications_path'], Maestrano::param('webhook.connec.notifications_path'));
      $this->assertEquals($this->config['webhook']['connec']['subscriptions'], Maestrano::param('webhook.connec.subscriptions'));
    }
    
    public function testBindingConfigurationBooleanViaJson() {
      $config = array('environment' => 'production', 'sso' => array('enabled' => false));
      Maestrano::configure(json_decode(json_encode($config),true));
      
      $this->assertFalse(Maestrano::param('sso.enabled'));
    }
    
    public function testConfigurationFromFile() {
      $path = "config.json";
      file_put_contents($path,json_encode($this->config));
      
      Maestrano::configure($path);
      $this->assertEquals($this->config['environment'], Maestrano::param('environment'));
      $this->assertEquals($this->config['app']['host'], Maestrano::param('app.host'));
      $this->assertEquals($this->config['api']['id'], Maestrano::param('api.id'));
      $this->assertEquals($this->config['api']['key'], Maestrano::param('api.key'));
      $this->assertEquals($this->config['sso']['init_path'], Maestrano::param('sso.init_path'));
      $this->assertEquals($this->config['sso']['consume_path'], Maestrano::param('sso.consume_path'));
      $this->assertEquals($this->config['webhook']['account']['groups_path'], Maestrano::param('webhook.account.groups_path'));
      $this->assertEquals($this->config['webhook']['account']['group_users_path'], Maestrano::param('webhook.account.group_users_path'));
      
      unlink($path);
    }
    
    public function testAuthenticateWhenValid() {
      Maestrano::configure($this->config);
      
      $this->assertTrue(Maestrano::authenticate($this->config['api']['id'],$this->config['api']['key']));
    }
    
    public function testAuthenticateWhenInvalid() {
      Maestrano::configure($this->config);
      
      $this->assertFalse(Maestrano::authenticate($this->config['api']['id'] . "aaa",$this->config['api']['key']));
      $this->assertFalse(Maestrano::authenticate($this->config['api']['id'],$this->config['api']['key'] . "aaa"));
    }
    
    public function testToMetadata() {
      Maestrano::configure($this->config);
      
      $expected = array(
        'environment'        => $this->config['environment'],
        'app' => array(
          'host'             => $this->config['app']['host']
        ),
        'api' => array(
          'id'               => $this->config['api']['id'],
          'version'          => Maestrano::VERSION,
          'verify_ssl_certs' => false,
          'lang'             => 'php',
          'lang_version'     => phpversion() . " " . php_uname(),
          'host'             => Maestrano::$EVT_CONFIG[$this->config['environment']]['api.host'],
          'base'             => Maestrano::$EVT_CONFIG[$this->config['environment']]['api.base'],
        ),
        'sso' => array(
          'enabled'          => true,
          'slo_enabled'      => true,
          'init_path'        => $this->config['sso']['init_path'],
          'consume_path'     => $this->config['sso']['consume_path'],
          'creation_mode'    => 'real',
          'idm'              => $this->config['app']['host'],
          'idp'              => $this->config['sso']['idp'],
          'name_id_format'   => Maestrano::$EVT_CONFIG[$this->config['environment']]['sso.name_id_format'],
          'x509_fingerprint' => Maestrano::$EVT_CONFIG[$this->config['environment']]['sso.x509_fingerprint'],
          'x509_certificate' => Maestrano::$EVT_CONFIG[$this->config['environment']]['sso.x509_certificate'],
        ),
        'connec' => array(
          'host'             => Maestrano::$EVT_CONFIG[$this->config['environment']]['connec.host'],
          'base_path'        => Maestrano::$EVT_CONFIG[$this->config['environment']]['connec.base_path']
        ),
        'webhook' => array(
          'account' => array(
            'groups_path' => $this->config['webhook']['account']['groups_path'],
            'group_users_path' => $this->config['webhook']['account']['group_users_path'],
          ),
          'connec' => array(
            'notifications_path' => $this->config['webhook']['connec']['notifications_path'],
            'subscriptions' => $this->config['webhook']['connec']['subscriptions']
          )
        )
      );
      
      $this->assertEquals(json_encode($expected),Maestrano::toMetadata());
    }
}


?>