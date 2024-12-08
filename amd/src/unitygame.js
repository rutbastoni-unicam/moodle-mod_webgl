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

    const setGameProgress = (progressData) => {
        window.console.error('>>>WORK IN PROGRESS - THIS SHOULD MARK SOME OF THE UNITY GAME ACTIVITY PROGRESS');
        window.console.log(progressData);
    };

    window.mod_webgl_plugin.trackGameViewed = setGameLoaded;
    window.mod_webgl_plugin.trackGameProgress = setGameProgress;

    window.console.error('>>>unityGame setup ready');
    window.mod_webgl_plugin.initted = true;
};