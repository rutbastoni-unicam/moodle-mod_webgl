//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/messages/>.

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
    const setGameLoaded = () => {
        window.console.error('>>>WORK IN PROGRESS - THIS SHOULD SET THE UNITY GAME ACTIVITY AS VIEWED');
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