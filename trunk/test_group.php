<?php

// filtre unique
$filter['unique']=array(
  array(
    'filter' => 'uid=a*',
    'object_type' => 'LSeepeople',
  )
);

// UniqueMember group
$filter['uniqueMember']=array(
  array(
    'filter' => 'cn=admin*',
    'object_type' => 'LSeegroup',
    'attr' => 'uniqueMember',
    'basedn' => 'o=ost'
  ),
  array(
    'basedn' => '%{dn}'
  )
);

// memberUid
$filter['memberUid']=array(
  array(
    'filter' => 'objectClass=posixGroup',
    'attr' => 'memberUid',
    'basedn' => 'o=ost'
  ),
  array(
    'filter' => 'uid=%{dn}',
  )
);


/*
- On liste les memberUid
- on forme avec des DN et on recupère leur Groupe Principale
- on recupère tout les membres de ces groupes
- on ne garde que les ostpeople
*/
$filter['complexe']=array(
  array(
    'filter' => 'objectClass=posixGroup',
    'attr' => 'memberUid',
    'basedn' => 'o=ost'
  ),
  array(
    'filter' => 'uid=%{dn}',
    'basedn' => 'uid=%{dn},ou=people,o=ost',
    'attr' => 'gidNumber',
  ),
  array(
    'filter' => 'gidNumber=%{dn}',
    'object_type' => 'LSeegroup',
    'attr' => 'uniqueMember'
  ),
  array(
    'basedn' => "%{dn}",
  )
);



foreach($eepeople -> listObjects($filter['complexe'],'o=ost') as $obj){
  echo "DN : ".$obj -> dn."\n<br />";
  $obj -> debug_printAttrsValues();
}

?>