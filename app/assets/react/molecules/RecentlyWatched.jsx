import React from 'react';
import { useState, useEffect } from "react";
import SeriesGroupRecentlyWatched from "../organisms/SeriesGroupRecentlyWatched";

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

    // Group episodes by series
    const groupEpisodesBySeries = (episodes) => {
        const grouped = episodes.reduce((acc, episode) => {
            const seriesKey = episode.tvdbSeriesId || episode.seriesTitle;
            if (!acc[seriesKey]) {
                acc[seriesKey] = {
                    seriesTitle: episode.seriesTitle,
                    tvdbSeriesId: episode.tvdbSeriesId,
                    poster: episode.poster,
                    episodes: [],
                    overview: episode.overview || "No synopsis available"
                };
            }
            acc[seriesKey].episodes.push(episode);
            return acc;
        }, {});

        // Convert to array and sort episodes within each series
        return Object.values(grouped).map(series => ({
            ...series,
            episodes: series.episodes.sort((a, b) => {
                if (a.season !== b.season) {
                    return a.season - b.season;
                }
                return a.episode - b.episode;
            })
        }));
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

    const groupedSeries = groupEpisodesBySeries(recentlyWatchedData);

    return (
        <div>
            <div className={"bento"} style={{marginTop: '2rem'}}>
                <h2>Recently Watched ({recentlyWatchedData.length} episodes)</h2>
                <p style={{color: '#666', fontSize: '0.875rem', margin: '0.5rem 0 0 0'}}>
                    Made a mistake? Click "Unwatch" to move episodes back to your watchlist.
                </p>
            </div>
            {groupedSeries.map((seriesData) => (
                <SeriesGroupRecentlyWatched
                    key={seriesData.tvdbSeriesId || seriesData.seriesTitle}
                    seriesData={seriesData}
                    refreshState={handleRefresh}
                />
            ))}
        </div>
    )
}