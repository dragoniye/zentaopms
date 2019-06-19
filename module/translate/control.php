<?php
/**
 * The control file of translate of ZenTaoPMS.
 *
 * @copyright   Copyright 2009-2015 青岛易软天创网络科技有限公司(QingDao Nature Easy Soft Network Technology Co,LTD, www.cnezsoft.com)
 * @license     ZPL (http://zpl.pub/page/zplv12.html)
 * @author      Yidong Wang <yidong@cnezsoft.com>
 * @package     translate
 * @version     $Id$
 * @link        http://www.zentao.net
 */
class translate extends control
{
    /**
     * construct.
     * 
     * @param  string $moduleName 
     * @param  string $methodName 
     * @access public
     * @return void
     */
    public function __construct($moduleName = '', $methodName = '')
    {
        parent::__construct($moduleName, $methodName);
    }

    /**
     * Index method of translate. 
     * 
     * @param  string $zentaoVersion 
     * @access public
     * @return void
     */
    public function index()
    {
        $this->view->title      = $this->lang->translate->common;
        $this->view->position[] = $this->lang->translate->common;
        $this->display();
    }

    public function addLang()
    {
        if($_POST)
        {
            $response = array();
            $this->translate->addLang();
            if(dao::isError())
            {
                $response['result']  = 'fail';
                $response['message'] = dao::getError();
                $this->send($response);
            }

            $response['result']  = 'success';
            $response['message'] = $this->lang->saveSuccess;
            $response['locate']  = $this->createLink('translate', 'index');
            $this->send($response);
        }

        $referenceList = array();
        foreach($this->config->translate->defaultLang as $defaultLang)
        {
            if(!isset($this->config->langs[$defaultLang])) continue;
            $referenceList[$defaultLang] = $this->config->langs[$defaultLang];
        }
        $this->view->cmd = $this->translate->checkDirPriv();

        $this->view->title         = $this->lang->translate->addLang;
        $this->view->position[]    = html::a($this->createLink('translate', 'index'), $this->lang->translate->common);
        $this->view->position[]    = $this->lang->translate->addLang;
        $this->view->referenceList = $referenceList;
        $this->display();
    }

    /**
     * Show selected language.
     * 
     * @param  string $zentaoVersion 
     * @param  string $language 
     * @param  string $module 
     * @param  string $consultLang 
     * @access public
     * @return void
     */
    public function showLang($zentaoVersion = '3.0', $language = 'en', $module = '', $consultLang = 'en')
    {
        $langContent = '';
        $modules  = $this->translate->getModules($zentaoVersion);
        if(empty($module)) $module = current($modules);
        $filePath = $this->translate->getLangFilePath($zentaoVersion, $consultLang, $module);
        if(!in_array($language, $this->config->translate->defaultLang))
        {
            $this->view->translations = $this->translate->getTranslations($zentaoVersion, $language, $module);
        }
        else
        {
            $filePath    = $this->translate->getLangFilePath($zentaoVersion, $language, $module);
            $consultLang = $language;
        }

        if(file_exists($filePath)) $this->view->langHandle = fopen($filePath, "r");
        $this->view->percents      = $this->translate->getPercents($zentaoVersion, $language);
        $this->view->modules       = $modules;
        $this->view->module        = $module; 
        $this->view->language      = $language;
        $this->view->consultLang   = $consultLang;
        $this->view->zentaoVersion = $zentaoVersion;

        $this->display();
    }

    /**
     * Translate selected language.
     * 
     * @param  string $zentaoVersion 
     * @param  string $language 
     * @param  string $module 
     * @param  string $consultLang 
     * @access public
     * @return void
     */
    public function translate($zentaoVersion = '3.0', $language = 'en', $module = '', $consultLang = 'en')
    {
        $modules = $this->translate->getModules($zentaoVersion);
        if(empty($module)) $module = current($modules);
        if($this->app->user->account == 'guest') $this->locate($this->createLink('user', 'login'));
        if($_POST)
        {
            $this->translate->addTranslation($zentaoVersion, $language, $module);
            if(dao::isError()) die(js::error(dao::getError()));
            die(js::locate(inlink('showLang', "zentaoVersion=$zentaoVersion&language=$language&module=$module&consultLang=$consultLang"), 'parent'));
        }
        $filePath = $this->translate->getLangFilePath($zentaoVersion, $consultLang, $module);
        if(file_exists($filePath)) include($filePath);

        $this->view->percents      = $this->translate->getPercents($zentaoVersion, $language);
        $this->view->modules       = $modules;
        $this->view->module        = $module; 
        $this->view->language      = $language;
        $this->view->zentaoVersion = $zentaoVersion;
        $this->view->consultLang   = $consultLang;
        $this->view->moduleLangs   = $this->translate->untieModuleLang($lang);
        $this->view->translations  = $this->translate->getTranslations($zentaoVersion, $language, $module);

        unset($lang);
        $this->display();
    }

    /**
     * Download selected language.
     * 
     * @param  string $zentaoVersion 
     * @param  string $language 
     * @access public
     * @return void
     */
    public function download($zentaoVersion = '3.0', $language = 'en', $downloadModule = '')
    {
        $translateDir = $this->app->getBasePath() . "www/data/guard/zentaopms-$language" . date('His') . mt_rand(0, 100);
        $moduleDir    = $translateDir . '/module/';
        mkdir($translateDir);
        mkdir($moduleDir);
        $modules = $this->translate->getModules($zentaoVersion);
        foreach ($modules as $module)
        {
            if(!empty($downloadModule) and $downloadModule != $module) continue;
            $langPath = $moduleDir . $module . '/lang/';
            mkdir($moduleDir . $module);
            mkdir($langPath);
            $fileName = $langPath . $language . '.php';
            $filePath = $this->translate->getLangFilePath($zentaoVersion, 'en', $module);
            if(in_array($language, $this->config->translate->defaultLang))
            {
                $filePath = $this->translate->getLangFilePath($zentaoVersion, $language, $module);
                copy($filePath, $fileName);
                continue;
            }

            $translations = $this->translate->getTranslations($zentaoVersion, $language, $module);

            /* Write content for new lang file*/
            if(file_exists($filePath))
            {
                $langHandle = fopen($filePath, "r");
                $writeHandle = fopen($fileName, 'w');
                while(!feof($langHandle))
                {
                    $line     = fgets($langHandle);
                    $position = strpos($line, '=');
                    if($position !== false and strrpos(ltrim($line), '$lang->') === 0)
                    {
                        $leftContent  = substr($line, 0, $position);
                        $langKey      = str_replace(array("'", '$lang->'), array('"', ''), trim($leftContent));
                        $write        = $line;
                        if(isset($translations[$langKey]))
                        {
                            $translation = current($translations[$langKey]);
                            $write = $leftContent . "= '" . $translation->translation . "';\n";
                        }
                        fwrite($writeHandle, $write);
                    }
                    else
                    {
                        fwrite($writeHandle, $line);
                    }
                }
                fclose($langHandle);
            }
        }
        $downloadDir  = dirname($translateDir);
        $fileDir      = basename($translateDir);
        $fileName     = $fileDir . '.zip';
        $downloadFile = $downloadDir . '/' . $fileName;
        `cd $downloadDir; zip -rm -9 $fileName $fileDir`;
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) $fileName = urlencode($fileName);
        header('Content-Description: File Transfer');
        header('Content-type: application/octet-stream');
        header("Content-Disposition: attachment; filename=$fileName");
        header('Content-Length: ' . filesize($downloadFile));
        readfile($downloadFile);
        unlink($downloadFile);
        die();
    }

    public function ajaxVote($translateID)
    {
        $this->dao->update(TABLE_TRANSLATE)->set('vote = vote + 1')->where('id')->eq($translateID)->exec(false);
        $userVotes  = $this->cookie->votes;
        $userVotes .= $userVotes . "_" . $translateID . "_";
        setCookie('votes', $userVotes, $this->config->cookieLife);
        $translation = $this->dao->findByID($translateID)->from(TABLE_TRANSLATE)->fetch('', false);
        echo $translation->vote;
    }

    public function delete($translateID, $consultLang, $confirm = 'no')
    {
        if($confirm == 'no')
        {
            die(js::confirm($this->lang->translate->notice->delete, inlink('delete', "translateID=$translateID&consultLang=$consultLang&confirm=yes")));
        }
        $translation = $this->dao->findByID($translateID)->from(TABLE_TRANSLATE)->fetch('', false);

        $scores = $this->translate->countWords($translation->translation);
        $this->loadModel('score')->log($translation->account, 'deleteTranslate', 'punish', $scores, 'TRANSLATE');

        $this->dao->delete()->from(TABLE_TRANSLATE)->where('id')->eq($translateID)->exec(false);
        die(js::locate(inLink('showLang', "version=$translation->version&language=$translation->language&module=$translation->module&consultLang=$consultLang"), 'parent'));
    }
}