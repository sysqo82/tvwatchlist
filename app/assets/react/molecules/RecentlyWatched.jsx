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
                    episodes: []
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

    // Separate movies and episodes
    const movies = recentlyWatchedData.filter(item => item.isMovie);
    const episodes = recentlyWatchedData.filter(item => !item.isMovie);
    const groupedSeries = groupEpisodesBySeries(episodes);

    return (
        <div>
            <div className="bento recently-watched-header">
                <h2>Recently Watched ({recentlyWatchedData.length} {recentlyWatchedData.length === 1 ? 'item' : 'items'})</h2>
                <p className="recently-watched-subtitle">
                    Made a mistake? Click "Unwatch" to move episodes back to your watchlist.
                </p>
            </div>
            {movies.map((movie) => (
                <div key={movie.seriesTitle} className="bento mb-3 movie-item-recently-watched">
                    <div className="d-flex align-items-start gap-3">
                        <div className="flex-shrink-0">
                            <img 
                                src={movie.poster || '/build/images/fallback-image.png'} 
                                alt={movie.seriesTitle}
                                className="series-poster"
                                onError={(e) => {
                                    e.target.onerror = null;
                                    e.target.src = '/build/images/fallback-image.png';
                                }}
                            />
                        </div>
                        <div className="flex-grow-1">
                            <div className="d-flex justify-content-between align-items-start mb-2">
                                <div className="flex-grow-1">
                                    <h3 className="mb-1 text-success">
                                        {movie.seriesTitle}
                                    </h3>
                                    <div className="mb-2">
                                        <span className="badge bg-success">Movie</span>
                                        <span className="text-success ms-2">✓ Watched on {new Date(movie.watchedAt).toLocaleDateString('en-GB')}</span>
                                    </div>
                                </div>
                                <div className="d-flex flex-column gap-2">
                                    <button 
                                        className="btn btn-sm btn-warning"
                                        onClick={() => {
                                            if (confirm(`Unwatch "${movie.seriesTitle}"? It will go back to your watchlist as unwatched.`)) {
                                                fetch(`/api/movies/${movie.movieId}/unwatch`, {
                                                    method: 'PATCH'
                                                }).then(() => handleRefresh());
                                            }
                                        }}
                                    >
                                        Unwatch
                                    </button>
                                    <button 
                                        className="btn btn-sm btn-danger"
                                        onClick={() => {
                                            if (confirm(`Archive "${movie.seriesTitle}"? It will be moved to the Archive section.`)) {
                                                // Archive the movie
                                                fetch(`/api/movies/${movie.movieId}/archive`, {
                                                    method: 'POST'
                                                }).then(() => handleRefresh());
                                            }
                                        }}
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                            <p className="text-muted mb-0 small">{movie.description || 'No synopsis available'}</p>
                        </div>
                    </div>
                </div>
            ))}
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