import React from 'react';
import { useState } from "react";
import UpNext from "../molecules/UpNext";
import Ingest from "../molecules/Ingest";
import Archive from "../pages/Archive";

export default function Application() {
    const upNextForm = 1;
    const ingestForm = 2;
    const archiveForm = 3;
    const [state, updateState] = useState(1);

    return (
        <div>
            <h1 className="text-center">TV Watchlist</h1>
            
            {/* Navigation buttons */}
            <div className="component d-flex gap-2 justify-content-center flex-wrap">
                {state !== upNextForm && (
                    <button 
                        className="btn btn-lg btn-outline-primary" 
                        type="button" 
                        onClick={() => updateState(upNextForm)}
                    >
                        ← Back to Watchlist
                    </button>
                )}
                
                {state === upNextForm && (
                    <>
                        <button 
                            className="btn btn-lg btn-primary" 
                            type="button" 
                            onClick={() => updateState(ingestForm)}
                        >
                            Add a Show
                        </button>
                        <button 
                            className="btn btn-lg btn-outline-secondary" 
                            type="button" 
                            onClick={() => updateState(archiveForm)}
                        >
                            📁 Archive
                        </button>
                    </>
                )}
            </div>

            {/* Content sections */}
            <div>
                {state === upNextForm && (
                    <UpNext />
                )}
                {state === ingestForm && (
                    <Ingest />
                )}
                {state === archiveForm && (
                    <Archive />
                )}
            </div>
        </div>
    );
}
