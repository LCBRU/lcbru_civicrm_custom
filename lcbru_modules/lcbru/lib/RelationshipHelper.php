<?php

/**
 * This class provides utility functions for working with Relationships.
*/

class RelationshipHelper
{
  public function getRelationshipTypeFromName($name) {
    Guard::AssertString_NotEmpty('$name', $name);

    return CiviCrmApiHelper::getObjectOrNull('RelationshipType', array(
      'name_a_b' => $name
      ));
  }

  public function getRelationshipOfTypeForContact($contact_id_a, $relationship_type) {
    Guard::AssertInteger('$contact_id_a', $contact_id_a);
    Guard::AssertString_NotEmpty('$relationship_type', $relationship_type);

    $relationshipType = $this->getRelationshipTypeFromName($relationship_type);

    Guard::AssertArray_NotEmpty('$relationshipType', $relationshipType);

    return CiviCrmApiHelper::getObjectOrNull('Relationship', array(
      'contact_id_a' => $contact_id_a,
      'relationship_type_id' => $relationshipType['id']
      ));
  }

  public function createRelationship($contact_id_a, $contact_id_b, $relationship_type, $case_id) {
    Guard::AssertInteger('$contact_id_a', $contact_id_a);
    Guard::AssertInteger('$contact_id_b', $contact_id_b);
    Guard::AssertString_NotEmpty('$relationship_type', $relationship_type);
    Guard::AssertInteger('$case_id', $case_id);

    $relationshipType = $this->getRelationshipTypeFromName($relationship_type);

    Guard::AssertArray_NotEmpty('$relationshipType', $relationshipType);

    $existing = $this->getRelationship(array(
        'contact_id_a' => $contact_id_a,
        'relationship_type_id' => $relationshipType['id'],
        'case_id' => $case_id,
        'is_active' => '1',
    ));

    if (!is_null($existing)) {
      CiviCrmApiHelper::deleteObject('Relationship', $existing);
    }

    CiviCrmApiHelper::createObject('Relationship', array(
        'contact_id_a' => $contact_id_a,
        'contact_id_b' => $contact_id_b,
        'relationship_type_id' => $relationshipType['id'],
        'case_id' => $case_id,
        'is_active' => '1',
        ));
  }

  public function getRelationship($params) {
    Guard::AssertArray('$params', $params);

    return CiviCrmApiHelper::getObjectOrNull('Relationship', $params);
  }
}
