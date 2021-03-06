<?php
// **********************************************************************
//
// Copyright (c) 2003-2017 ZeroC, Inc. All rights reserved.
//
// This copy of Ice is licensed to you under the terms described in the
// ICE_LICENSE file included in this distribution.
//
// **********************************************************************
//
// Ice version 3.7.0
//
// <auto-generated>
//
// Generated from file `IPhamCastCalculator.ice'
//
// Warning: do not edit this file.
//
// </auto-generated>
//


namespace
{
    require_once 'Cryosoft.php';
}

namespace Cryosoft\PhamCastCalculator
{
    global $Cryosoft_PhamCastCalculator__t_IPhamCastCalculator;
    global $Cryosoft_PhamCastCalculator__t_IPhamCastCalculatorPrx;

    class IPhamCastCalculatorPrxHelper
    {
        public static function checkedCast($proxy, $facetOrContext=null, $context=null)
        {
            return $proxy->ice_checkedCast('::Cryosoft::PhamCastCalculator::IPhamCastCalculator', $facetOrContext, $context);
        }

        public static function uncheckedCast($proxy, $facet=null)
        {
            return $proxy->ice_uncheckedCast('::Cryosoft::PhamCastCalculator::IPhamCastCalculator', $facet);
        }

        public static function ice_staticId()
        {
            return '::Cryosoft::PhamCastCalculator::IPhamCastCalculator';
        }
    }

    $Cryosoft_PhamCastCalculator__t_IPhamCastCalculator = IcePHP_defineClass('::Cryosoft::PhamCastCalculator::IPhamCastCalculator', '\\Cryosoft\\PhamCastCalculator\\IPhamCastCalculator', -1, false, true, $Ice__t_Value, null);

    $Cryosoft_PhamCastCalculator__t_IPhamCastCalculatorPrx = IcePHP_defineProxy('::Cryosoft::PhamCastCalculator::IPhamCastCalculator', $Ice__t_ObjectPrx, null);

    IcePHP_defineOperation($Cryosoft_PhamCastCalculator__t_IPhamCastCalculatorPrx, 'PCCCalculation', 0, 0, 0, array(array($Cryosoft__t_stSKConf), array($IcePHP__t_bool)), null, array($IcePHP__t_long), null);
}
?>
