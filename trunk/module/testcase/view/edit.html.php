<?php
/**
 * The edit file of case module of ZenTaoMS.
 *
 * ZenTaoMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * ZenTaoMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with ZenTaoMS.  If not, see <http://www.gnu.org/licenses/>.  
 *
 * @copyright   Copyright 2009-2010 青岛易软天创网络科技有限公司(www.cnezsoft.com)
 * @author      Chunsheng Wang <chunsheng@cnezsoft.com>
 * @package     case
 * @version     $Id$
 * @link        http://www.zentaoms.com
 */
?>
<?php include './header.html.php';?>
<style>#module{width:90%}</style>
<form method='post' target='hiddenwin'>
<div class='yui-d0'>
  <div id='titlebar'>
    <div id='main'>
    CASE #<?php echo $case->id . $lang->colon;?>
    <?php echo html::input('title', $case->title, 'class=text-1');?>
    </div>
    <div><?php echo html::submitButton();?></div>
  </div>
</div>

<div class='yui-d0 yui-t8'>
  <div class='yui-main'>
    <div class='yui-b'>
      <fieldset>
        <legend><?php echo $lang->testcase->legendSteps;?></legend>
        <div class='a-center'>
          <table class='table-1 bd-1px'>
            <tr class='colhead'>
              <th class='w-30px'><?php echo $lang->testcase->stepID;?></th>
              <th><?php echo $lang->testcase->stepDesc;?></th>
              <th><?php echo $lang->testcase->stepExpect;?></th>
              <th class='w-100px'><?php echo $lang->actions;?></th>
            </tr>
            <?php
            foreach($case->steps as $stepID => $step)
            {
                $stepID += 1;
                echo "<tr id='row$stepID' class='a-center'>";
                echo "<th class='stepID'>$stepID</th>";
                echo '<td class="w-p50">' . html::textarea('steps[]', $step->desc, "class='w-p100'") . '</td>';
                echo '<td>' . html::textarea('expects[]', $step->expect, "class='w-p100'") . '</td>';
                echo "<td class='a-center w-100px'><nobr>";
                echo "<input type='button' tabindex='-1' class='addbutton' onclick='preInsert($stepID)'  value='{$lang->testcase->insertBefore}' /> ";
                echo "<input type='button' tabindex='-1' class='addbutton' onclick='postInsert($stepID)' value='{$lang->testcase->insertAfter}'  /> ";
                echo "<input type='button' tabindex='-1' class='delbutton' onclick='deleteRow($stepID)'  value='{$lang->testcase->deleteStep}'   /> ";
                echo "</nobr></td>";
                echo '</tr>';
            }
            ?>
          </table>
        </div>
      </fieldset>

      <fieldset>
        <legend><?php echo $lang->testcase->legendComment;?></legend>
        <textarea name='comment' rows='6' class='w-p100'></textarea>
      </fieldset>
      <!--
      <fieldset>
        <legend><?php echo $lang->testcase->legendAttatch;?></legend>
        <?php echo $this->fetch('file', 'buildform', 'filecount=2');?>
      </fieldset>
      -->

      <div class='a-center'>
       <?php echo html::submitButton();?>
       <input type='button' value='<?php echo $lang->testcase->buttonToList;?>' class='button-s' 
            onclick='location.href="<?php echo $this->createLink('testcase', 'browse', "productID=$productID");?>"' />
      </div>
      <?php include '../../common/view/action.html.php';?>

    </div>
  </div>

  <div class='yui-b'>
    <fieldset>
      <legend><?php echo $lang->testcase->legendBasicInfo;?></legend>
      <table class='table-1 a-left' cellpadding='0' cellspacing='0'>
        <tr>
          <td class='rowhead w-p20'><?php echo $lang->testcase->product;?></td>
          <td><?php echo html::select('product', $products, $productID, "onchange=loadAll(this.value); class='select-1'");?></td>
        </tr>
        <tr>
          <td class='rowhead'><?php echo $lang->testcase->module;?></td>
          <td><span id='moduleIdBox'><?php echo html::select('module', $moduleOptionMenu, $currentModuleID, "class='select-1'");?></span></td>
        </tr>
        <tr>
          <td class='rowhead'><?php echo $lang->testcase->story;?></td>
          <td class='a-left'><span id='storyIdBox'><?php echo html::select('story', $stories, $case->story, 'class=select-1');?></span></td>
        </tr>
        <tr>
          <td class='rowhead'><?php echo $lang->testcase->type;?></td>
          <td><?php echo html::select('type', (array)$lang->testcase->typeList, $case->type, 'class=select-1');?>
        </tr>
        <tr>
          <th class='rowhead'><?php echo $lang->testcase->stage;?></th>
          <td><?php echo html::select('stage[]', $lang->testcase->stageList, $case->stage, "class='select-1' multiple='multiple'");?></td>
        </tr>  
        <tr>
          <td class='rowhead'><?php echo $lang->testcase->pri;?></td>
          <td><?php echo html::select('pri', (array)$lang->testcase->priList, $case->pri, 'class=select-1');?>
        </tr>
        <tr>
          <td class='rowhead'><?php echo $lang->testcase->status;?></td>
          <td><?php echo html::select('status', (array)$lang->testcase->statusList, $case->status, 'class=select-1');?></td>
        </tr>
        <tr>
          <td class='rowhead'><?php echo $lang->testcase->keywords;?></td>
          <td><?php echo html::input('keywords', $case->keywords, 'class=text-1');?></td>
        </tr>
        <tr>
          <td class='rowhead'><?php echo $lang->testcase->linkCase;?></td>
          <td><?php echo html::input('linkCase', $case->linkCase, 'class=text-1');?></td>
        </tr>
      </table>
    </fieldset>
    <fieldset>
      <legend><?php echo $lang->testcase->legendOpenAndEdit;?></legend>
      <table class='table-1 a-left'>
        <tr>
          <td class='rowhead w-p20'><?php echo $lang->testcase->openedBy;?></td>
          <td><?php echo $case->openedBy . $lang->at . $case->openedDate;?></td>
        <tr>
          <td class='rowhead'><?php echo $lang->testcase->lblLastEdited;?></td>
          <td><?php if($case->lastEditedBy) echo $case->lastEditedBy . $lang->at . $case->lastEditedDate;?></td>
        </tr>
      </table>
    </fieldset>
  </div>
</div>
<?php include '../../common/view/footer.html.php';?>
