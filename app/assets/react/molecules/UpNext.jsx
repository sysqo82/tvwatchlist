import React from 'react';
import { useState, useEffect } from "react";
import ShowUpNext from "../organisms/ShowUpNext";

export default function UpNext() {
    const [episodeData, setEpisodeData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [showIngestLink, setShowIngestLink] = useState(false);

    function refreshState() {
        fetch(`/api/nextup`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json+ld"
            }
        })
            .then((response) => {
                if(!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then((episodeData) => {
                if(episodeData.length === 0) {
                    setShowIngestLink(true);
                    setEpisodeData(null);
                    return;
                }
                setEpisodeData(episodeData);
                setError(null);
            })
            .catch((err) => {
                setError(err.message);
                setEpisodeData(null);
            })
            .finally(() => {
                setLoading(false);
            });
    }

    useEffect(() => { refreshState(); }, []);

    return (
        <div className={"bento"}>
            {showIngestLink && (
                <h1 id="nothing-found">No shows found</h1>
            )}
            {loading && <div>Loading...</div>}
            {error && (
                <div>{`There is a problem fetching the post data - ${error}`}</div>
            )}
            {episodeData &&
                (<ShowUpNext episodeData={episodeData} refreshState={refreshState}/>)
            }
        </div>
    )
}
