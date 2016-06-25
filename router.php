<?php
/**
 * @version     3.0.0
 * @package     com_datsogallery
 * @copyright   Copyright (C) 2015. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Andrey Datso <admin@datso.fr> - http://www.datso.fr
 */

// No direct access
defined('_JEXEC') or die;

/*function DatsoGalleryBuildRoute(&$query)
{
       $segments = array();
       if (isset($query['view']))
       {
                $segments[] = $query['view'];
                unset($query['view']);
       }
       if (isset($query['catid']))
       {
                $segments[] = getPath($query['catid']);
                unset($query['catid']);
       }
       if (isset($query['id']))
       {
                $segments[] = getPhoto($query['id']);
                unset($query['id']);
       };
       unset($query['view']);
       return $segments;
}

function DatsoGalleryParseRoute($segments)
{
       $vars = array();
       switch($segments[0])
       {
               case 'categories':
                       $vars['view'] = 'categories';
                       break;
               case 'category':
                       $vars['view'] = 'category';
                       $id = explode(':', $segments[1]);
                       $vars['catid'] = (int) $id[0];
                       break;
               case 'image':
                       $vars['view'] = 'image';
                       $catid = explode(':', $segments[1]);
                       $id = explode(':', $segments[2]);
                       $vars['catid'] = (int) $catid[0];
                       $vars['id'] = (int) $id[0];
                       break;
       }
       return $vars;
}

function getPhoto($id) {
  if ((int) $id == 0) {
    return false;
  }
  $db = JFactory::getDbo();
  $query = $db->getQuery(true)
      ->select($db->qn('alias'))
      ->from($db->qn('#__datsogallery_images'))
      ->where($db->qn('id').' = '.(int) $id);
  $db->setQuery($query);
  $alias = $db->loadResult();
  return $id.':'.$alias;
}

function getPath($catid) {
  if ((int) $catid == 0) {
    return false;
  }
  $db = JFactory::getDbo();
  $query = $db->getQuery(true)
      ->select('path')
      ->from($db->qn('#__datsogallery_categories'))
      ->where($db->qn('id').' = '.(int) $catid)
      ->where($db->qn('alias').' != '.$db->q('root'));
  $db->setQuery($query);
  $path = $db->loadResult();
  return $catid.':'.$path;
}*/

function DatsoGalleryBuildRoute(&$query) {
  $sef_advanced = 1;
  $segments = array();
  if (isset ($query['view'])) {
    $view = $query['view'];
  }
  if (isset ($view) && ($view == 'category' || $view == 'image') && $sef_advanced) {
    if (isset ($query['catid'])) {
      $categories = getCategories($query['catid'],true);
      foreach ($categories as $category) {
        list($tmp,$category) = explode(':',$category,2);
        $segments[] = $category;
      }
      unset ($query['catid']);
    }
    if (isset ($query['id'])) {
      $image_alias = getPhoto($query['id']);
      list($tmp,$image_alias) = explode(':',$image_alias,2);
      $segments[] = $image_alias;
      unset ($query['id']);
    }
  }
  if (isset ($view)) {
    switch ($query['view']) {

      case 'category' :
        if (!$sef_advanced) {
          $segments[] = 'view-album';
          $segments[] = $query['catid'];
          unset ($query['catid']);
        }
        break;

      case 'image' :
        if (!$sef_advanced) {
          $segments[] = 'view-photo';
          if (isset ($query['catid'])) {
            $segments[] = $query['catid'];
            unset ($query['catid']);
          }
          if (isset ($query['id'])) {
            $segments[] = $query['id'];
            unset ($query['id']);
          }
        }
        break;

      case 'upload' :
        $segments[] = 'add-photo';
        break;

      case 'favorites' :
        $segments[] = 'my-favorites';
        break;

      case 'popular' :
        $segments[] = 'popular-photos';
        break;

      case 'rating' :
        $segments[] = 'best-rated';
        break;

      case 'downloads' :
        $segments[] = 'most-downloaded';
        break;

      case 'lastadded' :
        $segments[] = 'last-added';
        break;

      case 'lastcommented' :
        $segments[] = 'last-commented';
        break;

      case 'search' :
        $segments[] = 'search-results';
        break;

      case 'purchases' :
        $segments[] = 'my-purchases';
        break;

      case 'checkout' :
        $segments[] = 'checkout';
        break;

      case 'complete' :
        $segments[] = 'complete';
        break;

      case 'cancel' :
        $segments[] = 'cancel';
        break;

      case 'tag' :
        $segments[] = 'tag';
        if (isset ($query['tagval']))
          $segments[] = $query['tagval'];
        unset ($query['tagval']);
        break;

      case 'edit' :
        $segments[] = 'edit-photo';
        if (isset ($query['uid']))
          $segments[] = $query['uid'];
        unset ($query['uid']);
        break;

      case 'member' :
        $segments[] = 'images-by';
        if (isset ($query['id'])) {
          $username = getUsername($query['id']);
          list($tmp,$username) = explode(':',$username,2);
          $segments[] = $username;
          unset ($query['id']);
        }
        break;

      case 'profile' :
        $segments[] = 'user-profile';
        if (isset ($query['userid']))
          $segments[] = $query['userid'];
        unset ($query['userid']);
        break;
    }
  }

  unset ($query['view']);
  if (isset ($query['start'])) {
    $segments[] = 'page-'.$query['start'];
    unset ($query['start']);
  }
  if (isset ($query['limitstart'])) {
    unset ($query['limitstart']);
  }
  return $segments;
}

function DatsoGalleryParseRoute($segments) {
  $sef_advanced = 1;
  $db = JFactory::getDbo();
  $vars = array();
  $segments[0] = str_replace(':','-',$segments[0]);
  switch ($segments[0]) {

    case 'add-photo' :
      $vars['view'] = 'upload';
      break;

    case 'my-favorites' :
      $vars['view'] = 'favorites';
      if (isset ($segments[1])) {
        $vars['page'] = $segments[1];
      }
      break;

    case 'popular-photos' :
      $vars['view'] = 'popular';
      if (isset ($segments[1])) {
        $vars['page'] = $segments[1];
      }
      break;

    case 'best-rated' :
      $vars['view'] = 'rating';
      if (isset ($segments[1])) {
        $vars['page'] = $segments[1];
      }
      break;

    case 'most-downloaded' :
      $vars['view'] = 'downloads';
      if (isset ($segments[1])) {
        $vars['page'] = $segments[1];
      }
      break;

    case 'last-added' :
      $vars['view'] = 'lastadded';
      if (isset ($segments[1])) {
        $vars['page'] = $segments[1];
      }
      break;

    case 'last-commented' :
      $vars['view'] = 'lastcommented';
      if (isset ($segments[1])) {
        $vars['page'] = $segments[1];
      }
      break;

    case 'search-results' :
      $vars['view'] = 'search';
      if (isset ($segments[1])) {
        $vars['page'] = $segments[1];
      }
      break;

    case 'my-purchases' :
      $vars['view'] = 'purchases';
      if (isset ($segments[1])) {
        $vars['page'] = $segments[1];
      }
      break;

    case 'checkout' :
      $vars['view'] = 'checkout';
      break;

    case 'complete' :
      $vars['view'] = 'complete';
      break;

    case 'cancel' :
      $vars['view'] = 'cancel';
      break;

    case 'tag' :
      $vars['view'] = 'tag';
      $vars['tagval'] = $segments[1];
      if (isset ($segments[2])) {
        $vars['page'] = $segments[2];
      }
      break;

    case 'edit-photo' :
      $vars['view'] = 'editpic';
      $vars['uid'] = $segments[1];
      break;

    case 'images-by' :
      $vars['view'] = 'member';
      $query = $db->getQuery(true)
              ->select($db->qn('id'))
              ->from($db->qn('#__users'))
              ->where($db->qn('username').' = '.$db->q(str_replace(':','-',$segments[1])));
      $db->setQuery($query);
      $id = $db->loadResult();
      $vars['id'] = $id;
      //$vars['id'] = $segments[1];
      if (isset ($segments[2])) {
        $vars['limitstart'] = $segments[2];
      }
      break;

    case 'user-profile' :
      $vars['view'] = 'profile';
      $vars['userid'] = $segments[1];
      break;

    case 'view-album' :
      if (!$sef_advanced) {
        $vars['view'] = 'category';
        $vars['catid'] = $segments[1];
        if (isset ($segments[2])) {
          $vars['page'] = $segments[2];
        }
      }
      break;

    case 'view-photo' :
      if (!$sef_advanced) {
        $vars['view'] = 'image';
        if (isset ($segments[1])) {
          $vars['catid'] = $segments[1];
        }
        if (isset ($segments[2])) {
          $vars['id'] = $segments[2];
        }
      }
      break;
  }
  $views = array('my-photos','add-photo','my-favorites','popular-photos','best-rated',
                 'most-downloaded','last-added','last-commented','search-results',
                 'my-purchases','checkout','complete','cancel','tag','edit-photo',
                 'images-by');
  if ($sef_advanced) {
    $count = count($segments);
    if ($count && preg_grep("/page/i",@$segments)) {
      $found = 0;
      foreach ($segments as $segment) {
        $query = $db->getQuery(true)
            ->select(array('id','parent_id','alias'))
            ->from($db->qn('#__datsogallery_categories'));
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        foreach ($rows as $row) {
          if ($row->alias == str_replace(':','-',$segment)) {
            if (isset ($segments[$count-1])) {
              $vars['limitstart'] = $segments[$count-1];
            }
            $vars['catid'] = $row->id;
            $vars['view'] = 'category';
            $rows = $row->parent_id;
            $found = 1;
            break;
          }
        }
      }
      $found = 0;
    }
    else {
      $found = 0;
      foreach ($segments as $segment) {
        $query = $db->getQuery(true)
            ->select(array('id','parent_id','alias'))
            ->from($db->qn('#__datsogallery_categories'));
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        foreach ($rows as $row) {
          if ($row->alias == str_replace(':','-',$segment)) {
            $vars['catid'] = $row->id;
            $vars['view'] = 'category';
            $rows = $row->parent_id;
            $found = 1;
            break;
          }
        }
        if ($found == 0 && !preg_grep("/{$segments[0]}/",$views)) {
          $query = $db->getQuery(true)
              ->select($db->qn('id'))
              ->from($db->qn('#__datsogallery_images'))
              ->where($db->qn('catid').' = '.(int) $vars['catid'])
              ->where($db->qn('alias').' = '.$db->q(str_replace(':','-',$segment)));
          $db->setQuery($query);
          $id = $db->loadResult();
          $vars['id'] = $id;
          $vars['view'] = 'image';
          break;
        }
        $found = 0;
      }
    }
  }
  return $vars;
}

function getUsername($id) {
  if ((int) $id == 0) {
    return false;
  }
  $db = JFactory::getDbo();
  $query = $db->getQuery(true)
      ->select($db->qn('username'))
      ->from($db->qn('#__users'))
      ->where($db->qn('id').' = '.(int) $id);
  $db->setQuery($query);
  $alias = $db->loadResult();
  return $id.':'.$alias;
}

function getPhoto($id) {
  if ((int) $id == 0) {
    return false;
  }
  $db = JFactory::getDbo();
  $query = $db->getQuery(true)
      ->select($db->qn('alias'))
      ->from($db->qn('#__datsogallery_images'))
      ->where($db->qn('id').' = '.(int) $id);
  $db->setQuery($query);
  $alias = $db->loadResult();
  return $id.':'.$alias;
}

function getCategories($catid,$begin = false) {
  static $array = array();
  if ((int) $catid == 0) {
    return false;
  }
  if ($begin) {
    $array = array();
  }
  $db = JFactory::getDbo();
  $query = $db->getQuery(true)
      ->select(array('parent_id','alias'))
      ->from($db->qn('#__datsogallery_categories'))
      ->where($db->qn('id').' = '.(int) $catid)
      ->where($db->qn('alias').' != '.$db->q('root'));
  $db->setQuery($query);
  $rows = $db->loadObjectList();
  foreach ($rows as $row) {
    $alias = $catid.':'.$row->alias;
    array_push($array,$alias);
    getCategories($row->parent_id,false);
  }
  return array_reverse($array);
}
