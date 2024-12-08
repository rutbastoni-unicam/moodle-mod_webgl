//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/messages/>.
import {call as fetchMany} from 'core/ajax';
import $ from 'jquery';

/**
 * Handle progress game from Unity interface to Moodle
 *
 * @module     mod_webgl
 */
window.mod_webgl_plugin = {
    initted: false,
    trackGameViewed: () => {},
    trackGameProgress: (progressData) => {}
};

/**
 * @typedef {Object} ProgressData
 * @property {number} score - achieved game score
 * @property {number} completedLevels - number of completed game levels
 * @property {boolean} puzzleSolved - if the puzzle of this game has been solved
 */

export const init = () => {
    /**
     * Call to internal API to set this game as viewed
     */
    const setGameLoaded = async () => {
        const webglid = $('.webgl-iframe-content-loader').data('webgl');

        const response = await fetchMany([{
            methodname: 'mod_webgl_signal_game_loaded',
            args: {'webglid': webglid}
        }])[0];

        if (!response) {
            window.console.error('Error setting webgl ' + webglid + ' as viewed');
        }
        window.console.log(response);

    };

    /**
     *
      * @param {ProgressData} progressData
     * @returns {Promise<void>}
     */
    const setGameProgress = async (progressData) => {
        const webglid = $('.webgl-iframe-content-loader').data('webgl');
        window.console.log('Setting progress data object');
        window.console.log(progressData);

        //public static function signal_game_progress($webglid, $score, $completedlevels, $puzzlesolved) {
        const score = progressData?.score ? progressData.score : 0;
        const completedLevels = progressData?.completedLevels ? progressData.completedLevels : 0;
        const puzzleSolved = progressData?.puzzleSolved ? 1 : 0;

        const response = await fetchMany([{
            methodname: 'mod_webgl_signal_game_progress',
            args: {'webglid': webglid, 'score': score, 'completedlevels': completedLevels, 'puzzlesolved': puzzleSolved}
        }])[0];

        window.console.log('completed game? ' + response);
        if (response) {
            // Completed activity so the user can return to the course
            $('#mod_webgl_course_url').submit();
        }

    };

    window.mod_webgl_plugin.trackGameViewed = setGameLoaded;
    window.mod_webgl_plugin.trackGameProgress = setGameProgress;

    window.console.error('>>>unityGame setup ready');
    window.mod_webgl_plugin.initted = true;
};