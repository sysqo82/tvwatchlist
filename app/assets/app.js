import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './react/App';
import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This is now a full Single Page Application powered by React.
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

// Mount the React app
const container = document.getElementById('root');
const root = createRoot(container);
root.render(<App />);