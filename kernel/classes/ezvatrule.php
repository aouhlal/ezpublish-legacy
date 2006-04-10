<?php
//
// Definition of eZVatRule class
//
// Created on: <17-Feb-2006 17:00:26 vs>
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: eZ publish
// SOFTWARE RELEASE: 3.8.x
// COPYRIGHT NOTICE: Copyright (C) 1999-2006 eZ systems AS
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//

/*!
  \class eZVatRule ezvatrule.php
  \brief eZVatRule handles VAT charging rules for the default VAT handler.
  \ingroup eZKernel
*/

require_once( 'kernel/classes/ezpersistentobject.php' );
require_once( 'lib/ezdb/classes/ezdb.php' );

class eZVatRule extends eZPersistentObject
{
    function eZVatRule( $row )
    {
        $this->ProductCategories = null;
        $this->eZPersistentObject( $row );
    }

    /**
     * \reimp
     */
    function definition()
    {
        return array( "fields" => array( "id" => array( 'name' => 'ID',
                                                        'datatype' => 'integer',
                                                        'default' => 0,
                                                        'required' => true ),
                                         "country" => array( 'name' => "Country",
                                                          'datatype' => 'string',
                                                          'default' => null,
                                                          'required' => true ),
                                         "vat_type" => array( 'name' => "VatType",
                                                              'datatype' => 'integer',
                                                              'default' => null,
                                                              'required' => true ) ),
                      "function_attributes" => array( 'product_categories' => 'productCategories',
                                                      'product_categories_string' => 'productCategoriesString',
                                                      'product_categories_ids' => 'productCategoriesIDs',
                                                      'product_categories_names' => 'productCategoriesNames',
                                                      'vat_type_object' => 'vatTypeObject',
                                                      'vat_type_name' => 'vatTypeName' ),
                      "keys" => array( "id" ),
                      "increment_key" => "id",
                      "class_name" => "eZVatRule",
                      "name" => "ezvatrule" );
    }

    /**
     * \reimp
     */
    function setAttribute( $attr, $val )
    {
        switch( $attr )
        {
            case 'product_categories':
            {
                $this->ProductCategories = $val;
            } break;

            default:
            {
                eZPersistentObject::setAttribute( $attr, $val );
            } break;
        }
    }

    function fetch( $id )
    {
        return eZPersistentObject::fetchObject( eZVatRule::definition(),
                                                null,
                                                array( "id" => $id ),
                                                true );
    }

    function fetchList()
    {
        return eZPersistentObject::fetchObjectList( eZVatRule::definition(),
                                                    null, null,
                                                    array( 'id' => 'asc' ),
                                                    null, true );
    }

    /**
     * Fetch VAT rules referencing given VAT type.
     *
     * \param $vatID ID of VAT type to count VAT rules for.
     * \public
     * \static
     */
    function fetchByVatType( $vatID )
    {
        return eZPersistentObject::fetchObjectList( eZVatRule::definition(), null,
                                                     array( 'vat_type' => (int) $vatID ),
                                                     null, null, true, false, null );
    }

    /**
     * Fetch number of VAT rules referencing given product category.
     *
     * \param $categories Category (single or list) to count VAT rules for.
     * \public
     * \static
     */
    function fetchCountByCategory( $categories )
    {
        $db =& eZDB::instance();

        $query = "SELECT COUNT(*) AS count FROM ezvatrule vr, ezvatrule_product_category vrpc " .
                 "WHERE vr.id=vrpc.vatrule_id AND vrpc.product_category_id";

        if ( is_array( $categories ) )
            $query .= " IN ('" . $db->implodeWithTypeCast( ',', $categories, 'int' ) . "')";
        else
            $query .= "=" . (int) $categories;

        $rows = $db->arrayQuery( $query );

        return $rows[0]['count'];
    }

    /**
     * Return number of VAT rules referencing given VAT type.
     *
     * \param $vatID ID of VAT type to count VAT rules for.
     * \public
     * \static
     */
    function fetchCountByVatType( $vatID )
    {
        $customFields = array( array( 'operation' => 'count( * )',
                                      'name' => 'count' ) );
        $rows = eZPersistentObject::fetchObjectList( eZVatRule::definition(), array(),
                                                     array( 'vat_type' => (int) $vatID ),
                                                     null, null, false, false,
                                                     $customFields );
        return $rows[0]['count'];
    }


    function create()
    {
        $row = array(
            "id" => null,
            "country" => null,
            "vat_type" => null
            );
        return new eZVatRule( $row );
    }

    /**
     * Remove given VAT charging rule.
     */
    function remove( $id )
    {
        $db =& eZDB::instance();

        $db->begin();

        // Remove product categories associated with the rule.
        eZVatRule::removeProductCategories( $id );

        // Remove the rule itself.
        eZPersistentObject::removeObject( eZVatRule::definition(), array( "id" => $id ) );

        $db->commit();
    }

    /**
     * \reimp
     */
    function store()
    {
        $db =& eZDB::instance();
        $db->begin();

        // Store the rule itself.
        eZPersistentObject::store();

        // Store product categories associated with the rule,
        $this->removeProductCategories();
        $categories = $this->attribute( 'product_categories' );
        if ( $categories )
        {
            foreach ( $categories as $category )
            {
                $query = sprintf( "INSERT INTO ezvatrule_product_category " .
                                  "(vatrule_id, product_category_id) VALUES (%d,%d)" ,
                                  $this->ID, $category['id'] );
                $db->query( $query );
            }
        }

        $db->commit();
    }

    /**
     * \private
     */
    function &productCategories()
    {
        // If product categories for this rule have not been fetched yet
        if ( $this->ProductCategories === null )
        {
            // fetch them
            $db =& eZDB::instance();
            $query = "SELECT pc.* FROM ezproductcategory pc, ezvatrule_product_category vrpc WHERE" .
                     " pc.id = vrpc.product_category_id AND" .
                     " vrpc.vatrule_id = {$this->ID}";
            $this->ProductCategories = $db->arrayQuery( $query );
        }

        return $this->ProductCategories;
    }

    /**
     * Return product categories as string, separated with commas.
     *
     * \private
     */
    function &productCategoriesString()
    {
        $categories = $this->attribute( 'product_categories' );
        if ( !$categories )
            return ezi18n( 'kernel/shop', 'Any' );

        $categoriesNames = array();
        foreach ( $categories as $cat )
            $categoriesNames[] = $cat['name'];

        $result = join( ', ', $categoriesNames );
        return $result;
    }

    /**
     * Return IDs of product categories associated with the rule.
     *
     * \private
     */
    function &productCategoriesIDs()
    {
        $catIDs = array();
        $categories = $this->attribute( 'product_categories' );

        if ( $categories )
        {
            foreach ( $categories as $cat )
                $catIDs[] = $cat['id'];
        }

        return $catIDs;
    }

    /**
     * Return names of product categories associated with the rule.
     *
     * \prrivate
     */
    function &productCategoriesNames()
    {
        $catNames = array();
        $categories = $this->attribute( 'product_categories' );

        if ( $categories )
        {
            foreach ( $categories as $cat )
                $catNames[] = $cat['name'];
        }

        return $catNames;
    }


    /**
     * Return VAT type name.
     */
    function &vatTypeName()
    {
        require_once( 'kernel/classes/ezvattype.php' );
        $vatType = eZVatType::fetch( $this->attribute( 'vat_type' ) );
        $name = $vatType->attribute( 'name' );
        return $name;
    }

    /**
     * Return VAT type object.
     */
    function &vatTypeObject()
    {
        require_once( 'kernel/classes/ezvattype.php' );
        $retObject = eZVatType::fetch( $this->attribute( 'vat_type' ) );
        return $retObject;
    }

    /**
     * Remove product categories with the rule from DB.
     *
     * \private
     * \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     * the calls within a db transaction; thus within db->begin and db->commit.
     */
    function removeProductCategories( $ruleID = false )
    {
        if ( $ruleID === false )
            $ruleID = $this->ID;

        $db =& eZDB::instance();
        $db->query( "DELETE FROM ezvatrule_product_category WHERE vatrule_id = $ruleID" );
    }

    /**
     * Remove references to the given product category.
     *
     * \public
     * \static
     * \note Transaction unsafe. If you call several transaction unsafe methods you must enclose
     * the calls within a db transaction; thus within db->begin and db->commit.
     */
    function removeReferencesToProductCategory( $cartegoryID )
    {
        $db =& eZDB::instance();
        $query = "DELETE FROM ezvatrule_product_category WHERE product_category_id=" . (int) $cartegoryID;
        eZDebug::writeDebug( $query, '$query' );
        $db->query( $query );
    }


    var $ProductCategories;
}

?>
