// import {submitGradingForm} from './repository';
import Selectors from './local/biblereader/selectors';
import {submitUserPreferences} from './repository';
import {submitPassageCompleted} from './repository';

const registerEventListeners = (uniqueid) => {
    document.addEventListener('click', e => {
        if (e.target.closest(Selectors.actions.togglePassageOptions)) {
            // window.console.log('[REQUESTED] toggle passage options');
            document.getElementById('passage_options_'+uniqueid).classList.toggle("hidden");
        }

        if (e.target.closest(Selectors.actions.savePassageOptions)) {
            // window.console.log('[REQUESTED] save passage options');
            document.getElementById('passage_options_'+uniqueid).classList.toggle("hidden");
            // doSomething();
            submitUserPreferences({ // const response =
              prefs: JSON.stringify({
                'font-size'   : document.getElementById('range_'+uniqueid).value,
                'translation' : document.getElementById('translations_'+uniqueid).value,
              })});
            // window.console.log(response);
        }
    });

    document.addEventListener('change', e => {

        if (e.target.closest(Selectors.actions.changeTranslation)) {
            // window.console.log('[REQUESTED] change translation');
            var queryParams = new URLSearchParams(window.location.search);
            queryParams.set("ver", document.getElementById('translations_'+uniqueid).value);
            window.location.href = window.location.href.split('?')[0] + '?' + queryParams.toString();
        }
    });

    document.addEventListener('input', e => {
        if (e.target.closest(Selectors.actions.updateRange)) {
            // window.console.log('[REQUESTED] update range');
            document.getElementById('pointSize_'+uniqueid).value = document.getElementById('range_'+uniqueid).value + ' points ';
            document.getElementById('passage_'+uniqueid).style.setProperty(
              `font-size`, document.getElementById('range_'+uniqueid).value + "pt");
            document.getElementById('reading_'+uniqueid).scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    /**
     *
     */
    function passageScrollCheck() {
     if(document.querySelector('.reading').scrollHeight - document.querySelector('.reading').scrollTop - window.innerHeight < 200) {
      // console.log("Bottom of mobile page");
      document.querySelector('.reading').removeEventListener('scroll', passageScrollCheck);
      document.querySelector('#page').removeEventListener('scroll', passageScrollCheck);

      var queryParams = new URLSearchParams(window.location.search);

      submitPassageCompleted({ // const response =
        prefs: queryParams.get("pageid")
        });
      }
    }

    /**
     *
     */
    function pageScrollCheck() {
      // console.log('desktop scroll check');
      if(document.querySelector('#page').scrollHeight - document.querySelector('#page').scrollTop - window.innerHeight < 200) {
      // console.log("Bottom of desktop page");
      document.querySelector('.reading').removeEventListener('scroll', pageScrollCheck);
      document.querySelector('#page').removeEventListener('scroll', pageScrollCheck);

      var queryParams = new URLSearchParams(window.location.search);

      submitPassageCompleted({ // const response =
        prefs: queryParams.get("pageid")
        });
      }
    }

    document.querySelector('#page').addEventListener('scroll', pageScrollCheck);
    document.querySelector('.reading').addEventListener('scroll', passageScrollCheck);
};

// export const biblereader = async() => {
export const init = (uniqueid) => {
    // window.console.log('biblereader console has been started up.');
    registerEventListeners(uniqueid);

    // update font size
    document.getElementById('pointSize_'+uniqueid).value = document.getElementById('range_'+uniqueid).value + ' points ';
    document.getElementById('passage_'+uniqueid).style.setProperty(
      `font-size`, document.getElementById('range_'+uniqueid).value + "pt");
    document.getElementById('reading_'+uniqueid).scrollTo({ top: 0, behavior: 'smooth' });

    // const assignmentId = getAssigmentId();
    // const response = await submitGradingForm(assignmentId, userId, data);
    // window.console.log('Unique ID: ' + e);
};

/*
export const example = () => {
  ajax.call([{
      methodname: 'mod_assign_submit_user_preferences',
      args: {userid: 1},
      // args: {assignmentid: assignmentid, userid: this._lastUserId, jsonformdata: JSON.stringify(data)},
      // done: this._handleFormSubmissionResponse.bind(this, data, nextUserId, nextUser),
      done: null,
      // fail: notification.exception
      fail: null
  }]);
};
*/

/*
export const doSomething = async() => {
    // ...
    // const assignmentId = getAssigmentId();
    const getUserId = 1; //getUserId();
    // const data = getData();

    // const response = await submitGradingForm(assignmentId, userId, data);
    const response = await submitGradingForm(userId);
    window.console.log(response);
}
*/
