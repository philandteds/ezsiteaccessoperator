<?php

class ezSiteAccessOperator
{
    function ezCurrentSiteAccessOperator()
    {
    }

    function operatorList()
    {
        return array('siteaccess');
    }

    function operatorTemplateHints()
    {
        return array( 'siteaccess' => array( 'input' => true,
                                              'output' => true,
                                              'parameters' => 1,
                                              'element-transformation' => true,
                                              'transform-parameters' => true,
                                              'input-as-parameter' => true,
                                              'element-transformation-func' => 'siteaccessTransformation'
                                            )
                     );
    }

   function siteaccessTransformation( $operatorName, &$node, $tpl, &$resourceData,
                                               $element, $lastElement, $elementList, $elementTree, &$parameters )
    {
        $paramCount = count( $parameters );
        if ( $paramCount < 0 ||
             $paramCount > 1 )
        {
            return false;
        }
        $values = array();
        $newElements = array();

        // Check that is $GLOBALS['eZCurrentAccess'] is set
        $code = <<<EOL
if( !isset( \$GLOBALS['eZCurrentAccess'] ) )
{
    \$tpl->error( "siteaccess", "Internal error. The eZCurrentAccess global does not exist." );
    %output% = false;
}
EOL;
        // If no parameters provided, return whole $GLOBALS['eZCurrentAccess']
        if( $paramCount === 0 )
        {
            $code .= <<<EOL
    %output% = \$GLOBALS["eZCurrentAccess"];
EOL;
        } else
        if( $paramCount === 1 )
        {
            // return $GLOBALS['eZCurrentAccess']['parametername']
            $code .= <<<EOL
if ( isset( \$GLOBALS['eZCurrentAccess'][%1%] ) )
{
    %output% = \$GLOBALS['eZCurrentAccess'][%1%];
}
else
{
    \$tpl->warning( "siteaccess", "Invalid input value: ( %1% ). Index is not found in eZCurrentAccess global " );
}
EOL;
            $values[] = $parameters[0];
        }

        $newElements[] = eZTemplateNodeTool::createCodePieceElement( $code, $values );
        return $newElements;
    }

    /*!
     See eZTemplateOperator::namedParameterList
    */
    function namedParameterList()
    {
        return array();
    }

    function modify( $tpl, $operatorName, $operatorParameters, $rootNamespace, $currentNamespace, &$operatorValue, $namedParameters,  $placement)
    {
        switch ( $operatorName )
        {
           case 'siteaccess':
            {
                if( !isset( $GLOBALS['eZCurrentAccess'] ) )
                {
                    $tpl->error( $operatorName, "Internal error. The eZCurrentAccess global does not exist.", $placement );
                    return false;
                }

                // Check if we got more than 1 parameter
                if ( $operatorValue === null and isset( $operatorParameters[1] ) )
                {
                    $tpl->error( $operatorName, "Too many parameters provided, only none or one accepted", $placement );
                    return false;
                }
                
                if ( $operatorValue === null and isset( $operatorParameters[0] ) )
                {
                    $operand = $tpl->elementValue( $operatorParameters[0], $rootNamespace, $currentNamespace, $placement );
                }
                else
                {
                    $operand = $operatorValue;
                }

                if( $operand === null )
                {
                    //no parameter given, return whole array
                    $operatorValue = $GLOBALS['eZCurrentAccess'];
                } 
                else
                {
                    if ( isset( $GLOBALS['eZCurrentAccess'][$operand] ) )
                    {
                        $operatorValue = $GLOBALS['eZCurrentAccess'][$operand];
                    }
                    else
                    {
                        $tpl->warning( $operatorName, "Invalid input value: ( $operand ). Index is not found in eZCurrentAccess global ", $placement );
                    }
                }
            } break;
         }
    }
}

?>
