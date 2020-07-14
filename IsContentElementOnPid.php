<?php
namespace Vendor\Ext\UserFunc;

use TYPO3\CMS\Core\Configuration\TypoScript\ConditionMatching\AbstractCondition;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

class IsContentElementOnPid extends AbstractCondition
{
    /**
     * Check if a plugin (CType) is used (hidden = 0) on the current page (pid):
     * [Vendor\Ext\UserFunc\IsContentElementOnCurrentPid = news]
     *
     * @param array $conditionParameters
     * @return bool
     */
    public function matchCondition(array $conditionParameters)
    {
        $pid = intval($GLOBALS['TSFE']->id);

        if ( empty($conditionParameters[0]) || $pid == 0 ) {
            return false;
        }

        $ce = preg_replace(
            "/[^a-zA-Z0-9_]/",
            "",
            trim(str_replace(
                '=',
                '',
                $conditionParameters[0]))
        );

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');

        /*
        * CType
        * */
        $count = $queryBuilder
            ->count('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'pid', $queryBuilder->createNamedParameter($pid, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'CType', $queryBuilder->createNamedParameter($ce, \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->eq(
                    'hidden', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'deleted', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetchColumn(0);

        if ($count > 0) {
            return true;
        }

        /*
        * Fluidcontent Element
        * */
        $count = $queryBuilder
            ->count('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'pid', $queryBuilder->createNamedParameter($pid, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'tx_fed_fcefile', $queryBuilder->createNamedParameter('Vendor.Ext:'.$ce.'.html', \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->eq(
                    'hidden', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'deleted', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetchColumn(0);

        if ($count > 0) {
            return true;
        }
        /*
         * CE is a Plugin (CType = list)
         * */
        $count = $queryBuilder
            ->count('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq(
                    'pid', $queryBuilder->createNamedParameter($pid, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'CType', $queryBuilder->createNamedParameter('list', \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->eq(
                    'list_type', $queryBuilder->createNamedParameter($ce, \PDO::PARAM_STR)
                ),
                $queryBuilder->expr()->eq(
                    'hidden', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'deleted', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetchColumn(0);

        if ($count > 0) {
            return true;
        }

        return false;

    }
}
