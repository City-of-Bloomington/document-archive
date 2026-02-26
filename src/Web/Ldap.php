<?php
/**
 * @copyright 2026 City of Bloomington, Indiana
 * @license https://www.gnu.org/licenses/agpl.txt GNU/AGPL, see LICENSE
 */
declare (strict_types=1);
namespace Web;

#ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);

class Ldap
{
    private ?\Ldap\Connection $connection = null;
    private array $config = [];
    public static $departments = [
        'UNKNOWN'        => '',
        'Clerk'          => 'OU=City Clerk,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'CFRD'           => 'OU=Community and Family Resources,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'Controller'     => 'OU=Controller,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'Council'        => 'OU=Council Office,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'ESD'            => 'OU=Economic & Sustainable Development,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'Engineering'    => 'OU=Engineering,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'HAND'           => 'OU=HAND,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'HR'             => 'OU=Human Resources,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'ITS'            => 'OU=ITS,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'Legal'          => 'OU=Legal,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'OOTM'           => 'OU=Office of the Mayor,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'Parks'          => 'OU=Parks and Recreation,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'Planning'       => 'OU=Planning and Transportation,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'Animal Shelter' =>     'OU=Animal Shelter,OU=Public Works,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'Facilities'     =>         'OU=Facilities,OU=Public Works,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'Fleet'          =>  'OU=Fleet Maintenance,OU=Public Works,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'Parking'        =>   'OU=Parking Services,OU=Public Works,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'Sanitation'     =>         'OU=Sanitation,OU=Public Works,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'Street'         => 'OU=Street and Traffic,OU=Public Works,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'Public Works'   =>                       'OU=Public Works,OU=City Hall,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'Fire'           => 'OU=Fire,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'Police'         => 'OU=Police,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov',
        'Utilities'      => 'OU=Utilities,OU=Departments,DC=cob,DC=bloomington,DC=in,DC=gov'
    ];

    public function __construct(array $conf)
    {
        if (!$this->connection) {
            if ($this->connection = ldap_connect($conf['server'])) {
                ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION,3);
                ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);
                if (!empty($conf['admin_binding'])) {
                    if (!ldap_bind(
                        $this->connection,
                        $conf['admin_binding'],
                        $conf['admin_pass']
                    )) {
                        throw new \Exception(ldap_error($this->connection));
                    }
                }
                else {
                    if (!ldap_bind($this->connection)) {
                        throw new \Exception(ldap_error($this->connection));
                    }
                }
            }
            else {
                throw new \Exception(ldap_error($this->connection));
            }
        }
        $this->config = $conf;
    }

    public function findUser(string $username): ?array
    {
        $result = ldap_search(
            $this->connection,
            $this->config['base_dn'],
            $this->config['username_attribute']."=$username"
        );
        if (ldap_count_entries($this->connection,$result)) {
            $entries = ldap_get_entries($this->connection, $result);
            return $entries[0];
        }
        return null;
    }


    public static function department(string $dn): ?string
    {
        foreach (self::$departments as $d=>$ou) {
            if (str_contains($dn, $ou)) { return $d; }
        }
        return null;
    }
}
