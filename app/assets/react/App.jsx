import React from 'react';
import { BrowserRouter, Routes, Route, Link, useNavigate } from 'react-router-dom';
import UpNext from './molecules/UpNext';
import Ingest from './molecules/Ingest';
import Archive from './pages/Archive';

function Navigation() {
    const navigate = useNavigate();
    const isHome = location.pathname === '/';
    
    return (
        <div>
            <h1 className="text-center">TV Watchlist</h1>
            
            <div className="component d-flex gap-2 justify-content-center flex-wrap">
                {!isHome && (
                    <button 
                        className="btn btn-lg btn-outline-primary" 
                        type="button" 
                        onClick={() => navigate('/')}
                    >
                        ← Back to Watchlist
                    </button>
                )}
                
                {isHome && (
                    <>
                        <button 
                            className="btn btn-lg btn-primary" 
                            type="button" 
                            onClick={() => navigate('/add')}
                        >
                            Add a Show
                        </button>
                        <button 
                            className="btn btn-lg btn-outline-secondary" 
                            type="button" 
                            onClick={() => navigate('/archive')}
                        >
                            📁 Archive
                        </button>
                    </>
                )}
            </div>
        </div>
    );
}

export default function App() {
    return (
        <BrowserRouter>
            <div className="custom-container">
                <Navigation />
                
                <Routes>
                    <Route path="/" element={<UpNext />} />
                    <Route path="/add" element={<Ingest />} />
                    <Route path="/archive" element={<Archive />} />
                </Routes>
            </div>
        </BrowserRouter>
    );
}
