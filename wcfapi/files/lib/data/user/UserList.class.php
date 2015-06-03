<?php
namespace api\data\user;
use api\data\IRESTfulObject;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\data\user\UserProfileList;
use wcf\system\WCF;
use wcf\util\DateUtil;
use wcf\util\StringUtil;
use wcf\system\exception\PermissionDeniedException;

/**
 * Returns a list of user profiles
 * 
 * @author	Simon Nußbaumer
 * @copyright	2015 Thurnax.com
 * @license	GNU Lesser General Public License <http://www.gnu.org/licenses/lgpl-3.0.txt>
 * @package	de.codequake.api
 */

class UserList extends UserProfileList implements IRESTfulObject {
	/**
	 * @see wcf\data\DatasbaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\user\UserProfileList';
	
	/**
	 * data fields for REST API
	 * @var array<string>
	 */
	public $fields  = array('userID', 'username', 'email', 'rank', 'avatar', 'onlineStatus', 'languageID', 'registrationDate');
	/**
	 * @see wcf\data\DatabaseObjectDecorator::__construct()
	 */
	public function __construct($objectID) {	
		$this->object = new static::$baseClass($objectID);
		$this->object->readObjects(); // reads objects from UserProfileList
	}
	
	/**
	 * @see api\data\IRESTfulObject::getAPIData()
	 */
	public function getAPIData() {
		$data = array();
		$users = $this->object->objects;
		foreach($users as $user) {
			foreach ($this->fields as $field) {
				switch($field) {
					case 'rank':
						$data[$user->userID][$field] = array(
							'rankID' => $user->rankID,
							'userTitle' => $data[$user->userID][$field] = $user->getUserTitle()
						);
					break;
					case 'avatar':
						$data[$user->userID][$field] = $user->getAvatar()->getURL();
					break;
					case 'onlineStatus':
						$data[$user->userID][$field] = array(
							'lastActivityTime' => array(
								'raw' => $user->lastActivityTime,
								'formatted' => DateUtil::format(DateUtil::getDateTimeByTimestamp($user->lastActivityTime), DateUtil::DATE_FORMAT) . ' ' . DateUtil::format(DateUtil::getDateTimeByTimestamp($user->lastActivityTime), DateUtil::TIME_FORMAT)
							)
						);
					break;
					case 'registrationDate':
						$data[$user->userID][$field] = array(
							'raw' => $user->{$field},
							'formatted' => DateUtil::format(DateUtil::getDateTimeByTimestamp($user->{$field}), DateUtil::DATE_FORMAT)
						);
					break;
					default:
						$data[$user->userID][$field] = $user->{$field};
					break;
				}
			}
			
			
			if (!$user->isAccessible('canViewEmailAddress')) {
				unset($data[$user->userID]['email']);
			}
			if (!$user->isAccessible('canSeeAvatar')) {
				unset($data[$user->userID]['avatar']);
			}
			if (!$user->isAccessible('canViewOnlineStatus')) {
				unset($data[$user->userID]['onlineStatus']);
			}
			
			if (!$user->isAccessible('canViewProfile')) {
				$data[$user->userID] = array();
			}
		}
		return $data;
	}
}
