import React from 'react';
import { useState, useEffect } from "react";
import RecentlyWatchedEpisode from "../organisms/RecentlyWatchedEpisode";

export default function RecentlyWatched({refreshTrigger, onRefresh}) {
    const [recentlyWatchedData, setRecentlyWatchedData] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    function refreshRecentlyWatched() {
        setLoading(true);
        fetch(`/api/recently-watched`, {
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
            .then((data) => {
                setRecentlyWatchedData(data);
                setError(null);
            })
            .catch((err) => {
                setError(err.message);
                setRecentlyWatchedData([]);
            })
            .finally(() => {
                setLoading(false);
            });
    }

    const handleRefresh = () => {
        refreshRecentlyWatched();
        if (onRefresh) {
            onRefresh(); // Also refresh the main watchlist
        }
    };

    useEffect(() => { 
        refreshRecentlyWatched(); 
    }, [refreshTrigger]);

    if (loading) {
        return (
            <div className={"bento"}>
                <div>Loading recently watched...</div>
            </div>
        );
    }

    if (error) {
        return (
            <div className={"bento"}>
                <div>Error loading recently watched: {error}</div>
            </div>
        );
    }

    if (recentlyWatchedData.length === 0) {
        return null; // Don't show the section if there are no recently watched episodes
    }

    return (
        <div>
            <div className={"bento"} style={{marginTop: '2rem'}}>
                <h2>Recently Watched ({recentlyWatchedData.length} episodes)</h2>
                <p style={{color: '#666', fontSize: '0.875rem', margin: '0.5rem 0 0 0'}}>
                    Made a mistake? Click "Unwatch" to move episodes back to your watchlist.
                </p>
            </div>
            {recentlyWatchedData.map((episode) => (
                <RecentlyWatchedEpisode 
                    key={episode.id} 
                    episodeData={episode} 
                    refreshState={handleRefresh}
                />
            ))}
        </div>
    )
}