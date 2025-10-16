import React from 'react';
import { useState } from "react";
import UpNext from "../molecules/UpNext";
import Ingest from "../molecules/Ingest";

export default function Application() {
    const upNextForm = 1;
    const ingestForm = 2;
    const [state, updateState] = useState(1);

    const switchState = () => {
        if(state === upNextForm) {
            updateState(ingestForm);
        } else {
            updateState(upNextForm);
        }
    }

    return (
        <div>
            <h1 className="text-center">TV Watchlist</h1>
            <div className="component">
                <button className="btn btn-lg btn-block btn-primary" type="button" id="navButton" onClick={switchState}>
                    {
                        state === upNextForm && (
                            "Add a show to watch"
                        )
                    }
                    {
                        state === ingestForm && (
                            "Back to What to watch"
                        )
                    }
                </button>
            </div>

            <div>
                {state === upNextForm && (
                    <UpNext />
                )}
                {state === ingestForm && (
                    <Ingest />
                )}
            </div>
        </div>
    );
}
