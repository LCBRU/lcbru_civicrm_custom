<?php

/**
 * This class provides utility functions for printing labels
 * 
 * Example usage:
 *
 * <code>
 *
 *   $blindIdRequirements = array (
 *       '[Blind Id Type]' => '[Prefix]'
 *   );
 *
 *   $bub = new BlindUnblind($studyName, $blindIdRequirements);
 *
 * // Blind a participant
 *
 *   $blindIds = $bub->blind($unblindId);
 *
 * // Unblind a participant
 *
 *   $unblindId = $bub->unblind($blindId);
 *
 * // Get participant Blind Ids
 *
 *   $blindIds = $bub->getBlindIds($unblindId);
 *
 * </code>
 * 
*/

class BlindUnblind
{

  public function __construct($studyName, $blindIdRequirements) {
    Guard::AssertString_NotEmpty('$studyName', $studyName);
    Guard::AssertArray('$blindIdRequirements', $blindIdRequirements);

    $this->studyName = $studyName;
    $this->blindIdRequirements = $blindIdRequirements;
  }

  public function blind($unblindId) {
    Guard::AssertString_NotEmpty('$unblindId', $unblindId);

  	foreach ($this->blindIdRequirements['blind_id_types'] as $blindIdType => $prefix) {
		$idGenerator = new IdGenerator($prefix);
		$blindId = $idGenerator->next();

		db_insert('blind_unblind_xref')
			->fields(array(
				'study' => $this->studyName,
				'unblind_id' => $unblindId,
				'blind_id_type' => $blindIdType,
				'blind_id' => $blindId,
				'uid' => $GLOBALS['user']->uid,
				'created' => REQUEST_TIME,
			))
			->execute();
   	}
  }

  public function unblind($blindId) {
    Guard::AssertString_NotEmpty('$blindId', $blindId);

  	return db_select('blind_unblind_xref', 'b')
		->fields('b', array('blind_id_type', 'unblind_id'))
		->condition('blind_id', $blindId, '=')
		->condition('study', $this->studyName, '=')
		->execute()
		->fetchAssoc();
  }

  public function getBlindIds($unblindId) {
    Guard::AssertString_NotEmpty('$unblindId', $unblindId);

  	return db_select('blind_unblind_xref', 'b')
		->fields('b', array('blind_id_type', 'blind_id'))
		->condition('unblind_id', $unblindId, '=')
		->condition('study', $this->studyName, '=')
		->orderBy('blind_id_type')
		->execute()
		->fetchAllKeyed();
  }

}
