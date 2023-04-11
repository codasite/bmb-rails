import App from "./App";
import { render } from '@wordpress/element';

/**
 * Import the stylesheet for the plugin.
 */
import './style/main.scss';

// Dynamically render components to avoid loading unused modules
const Settings = React.lazy(() => import('./components/settings/Settings'))

const page = wpbb_ajax_obj.page

if (page === 'settings') {
	// Render the App component into the DOM
	render(<App><Settings /></App>, document.getElementById('wpbb-admin-panel'));
}