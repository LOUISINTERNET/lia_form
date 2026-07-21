/**
 * Module: @lia/lia-form/backend/form-editor/view-model.js
 */
import * as StageComponent from '@typo3/form/backend/form-editor/stage-component.js';

/**
 * @private
 *
 * @var object
 */
var _formEditorApp = null;

/**
 * @private
 *
 * @return object
 */
function getFormEditorApp() {
  return _formEditorApp;
}

/**
 * @private
 *
 * @return object
 */
function getPublisherSubscriber() {
  return getFormEditorApp().getPublisherSubscriber();
}

/**
 * @private
 *
 * @return void
 */
function _subscribeEvents() {
  /**
   * @private
   *
   * @param string
   * @param array
   *              args[0] = formElement
   *              args[1] = template
   * @return void
   */
  getPublisherSubscriber().subscribe(
    "view/stage/abstract/render/template/perform",
    function (topic, args) {
      if (
        args[0].get("type") === "DataProtection" ||
        args[0].get("type") === "HtmlCode" ||
        args[0].get("type") === "PhoneAndAreaCode" ||
        args[0].get("type").indexOf("Lia") !== -1
      ) {
        StageComponent.renderSimpleTemplate(args[0], args[1]);
      }
    }
  );
}

/**
 * @public
 *
 * @param object formEditorApp
 * @return void
 */
export function bootstrap(formEditorApp) {
  _formEditorApp = formEditorApp;
  _subscribeEvents();
}
