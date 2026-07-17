import {call as fetchMany} from 'core/ajax';
/*
export const submitGradingForm = (
    assignmentid,
    userid,
    data,
) => fetchMany([{
    methodname: 'mod_assign_submit_user_preferences',
    args: {
        assignmentid,
        userid,
        jsonformdata: JSON.stringify(data),
    },
}])[0];
*/

/*
export const submitGradingForm = (
    userid,
) => fetchMany([{
    methodname: 'mod_biblereader_submit_user_preferences',
    args: {
        [{
          userid: 1,
        }]
    },
}])[0];
*/
export const submitPassageCompleted = (e
  ) => fetchMany([{
      methodname: 'mod_biblereader_passage_completed',
      args: {prefs: e.prefs},
      done: window.console.log('Stored passage as read.'),
      fail: window.console.log('Unable to store passage as read.'),
  }])[0];

export const submitUserPreferences = (e
  ) => fetchMany([{
      methodname: 'mod_biblereader_submit_user_preferences',
      args: {prefs: e.prefs},
      done: window.console.log('Preferences saved.'), // this._handleFormSubmissionResponse.bind(this, data, nextUserId, nextUser),
      fail: window.console.log('Unable to save preferences.'), // notification.exception
  }])[0];
