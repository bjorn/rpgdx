  <table cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
      <td class="boxHead">Project control panel</td>
    </tr>
    <tr>
      <td class="boxBody">
        [<a href="{PROJECT_PAGE_URL}">go to project page</a>]<br />
        [<a href="{EDIT_GENERAL_URL}">edit general information</a>]
      </td>
    </tr>
  </table>
  <div style="height: 5px; font-size: 1px;"></div>
  <table cellspacing="0" cellpadding="0" border="0" width="100%">
    <tr>
      <td width="50%" valign="top">
        <!-- BEGIN screenshots -->
        <table cellspacing="0" cellpadding="0" border="0" width="100%">
          <tr>
            <td class="boxHead">Screenshots</td>
          </tr>
          <tr>
            <td class="boxBody">
              <form action="{SCREENSHOT_REMOVE_TARGET}" method="post" style="margin: 0px; padding: 0px;">
              <input type="hidden" name="{PROJECT_ID_FIELD}" value="{PROJECT_ID}" />
              <table cellspacing="2" cellpadding="0" border="0">
                <!-- BEGIN shot -->
                <tr>
                  <td align="right" class="th" width="0"><input type="checkbox" name="{screenshots.shot.CHECK_FIELD}" value="{screenshots.shot.ID}"/></td>
                  <td class="td" valign="top" width="0">
                    <img src="{screenshots.shot.URL}" width="140" alt="screenshot" align="middle" class="screenshot" style="margin-left: 0px;" />
                  </td>
                  <td class="td" valign="middle">
                    <b>Caption:</b><br />
                    {screenshots.shot.CAPTION}<br />
                    <br />
                    <b>Size:</b><br />
                    {screenshots.shot.DIMENSIONS}
                  </td>
                </tr>
                <!-- END shot -->
                <tr>
                  <td align="right" class="th">&nbsp;</td>
                  <td class="td" colspan="2"><input type="submit" value="Remove selected" class="formButton" /></td>
                </tr>
              </table>
              </form>
            </td>
          </tr>
        </table>
        <div style="height: 5px; font-size: 1px;"></div>
        <!-- END screenshots -->
        <table cellspacing="0" cellpadding="0" border="0" width="100%">
          <tr>
            <td class="boxHead">Add a screenshot</td>
          </tr>
          <tr>
            <td class="boxBody">
              <div class="remark">You can upload screenshots of your project which will be shown on the project page. They can be no larger than 150 kb and need to be either PNG, GIF or JPG. You can add a maximum of {MAX_SCREENSHOTS} screenshots.</div>
              <br/>
              <form action="{SCREENSHOT_UPLOAD_TARGET}" method="post" enctype="multipart/form-data">
              <input type="hidden" name="{PROJECT_ID_FIELD}" value="{PROJECT_ID}" />
              <table cellspacing="2" cellpadding="0" border="0">
                <tr>
                  <td class="th" align="right" width="0">Caption:</td>
                  <td class="td"><input type="text" name="{SCREENSHOT_CAPTION_FIELD}" value="" class="formInput" maxlength="{SCREENSHOT_CAPTION_MAXLENGTH}" /></td>
                </tr>
                <tr>
                  <td class="th" align="right">File:</td>
                  <td class="td"><input type="file" name="{SCREENSHOT_FILE_FIELD}" value="" class="fileFormInput" /></td>
                </tr>
                <tr>
                  <td class="th">&nbsp;</td>
                  <td class="td"><input type="submit" value="Upload" class="formButton"/></td>
                </tr>
              </table>
              </form>
            </td>
          </tr>
        </table>
      </td>
      <td>
        <div style="width: 5px; font-size: 1px;"/>
      </td>
      <td width="50%" valign="top">
        <table cellspacing="0" cellpadding="0" border="0" width="100%">
          <tr>
            <td class="boxHead">Project icon</td>
          </tr>
          <tr>
            <td class="boxBody">
              <div class="remark">You can specify a 32x32 image to be used as icon for your project, which will be displayed in the project listings. It can be no larger than 10 kb and needs to be either PNG, GIF or JPG.</div>
              <br />
              <!-- BEGIN icon -->
              <img src="{icon.URL}" width="{icon.WIDTH}" height="{icon.HEIGHT}" align="middle" style="margin-left: 5px;" /><br />
              <br />
              <!-- END icon -->
              <form action="{ICON_UPLOAD_TARGET}" method="post" enctype="multipart/form-data">
              <input type="hidden" name="{PROJECT_ID_FIELD}" value="{PROJECT_ID}" />
              <table cellspacing="2" cellpadding="0" border="0">
                <tr>
                  <td class="th" width="0" align="right">File:</td>
                  <td class="td"><input type="file" name="{ICON_FILE_FIELD}" value="" class="fileFormInput" /></td>
                </tr>
                <tr>
                  <td class="th">&nbsp;</td>
                  <td class="td">
                    <input type="submit" name="upload" value="Upload" class="formButton"/>
                    <input type="submit" name="remove" value="Remove icon" class="formButton"/>
                  </td>
                </tr>
              </table>
              </form>
            </td>
          </tr>
          <tr>
            <td/>
          </tr>
        </table>
      </td>
    </tr>
  </table>
