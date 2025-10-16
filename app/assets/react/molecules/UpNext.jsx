import React from 'react';
import { useState, useEffect } from "react";
import SeriesGroup from "../organisms/SeriesGroup";
import RecentlyWatched from "./RecentlyWatched";

export default function UpNext() {
    const [episodeData, setEpisodeData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [loadingMore, setLoadingMore] = useState(false);
    const [error, setError] = useState(null);
    const [showIngestLink, setShowIngestLink] = useState(false);
    const [refreshTrigger, setRefreshTrigger] = useState(0);
    const [pagination, setPagination] = useState({ total: 0, limit: 50, offset: 0, hasMore: false });

    // Group episodes by series
    function groupEpisodesBySeries(episodes) {
        const grouped = {};
        
        episodes.forEach(episode => {
            const seriesKey = episode.tvdbSeriesId || episode.seriesTitle;
            
            if (!grouped[seriesKey]) {
                grouped[seriesKey] = {
                    seriesTitle: episode.seriesTitle,
                    tvdbSeriesId: episode.tvdbSeriesId,
                    poster: episode.poster,
                    overview: episode.overview || "No synopsis available", // Will be populated when we fetch series data
                    episodes: []
                };
            }
            
            grouped[seriesKey].episodes.push(episode);
        });
        
        // Sort episodes within each series by season and episode number
        Object.values(grouped).forEach(series => {
            series.episodes.sort((a, b) => {
                if (a.season !== b.season) {
                    return a.season - b.season;
                }
                return a.episode - b.episode;
            });
        });
        
        return Object.values(grouped);
    }

    function fetchEpisodes(offset = 0, append = false) {
        if (!append) setLoading(true);
        else setLoadingMore(true);
        
        fetch(`/api/nextup?limit=50&offset=${offset}`, {
            method: "GET",
            headers: {
                "Content-Type": "application/json"
            }
        })
            .then((response) => {
                if(!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then((data) => {
                if(data.episodes && data.episodes.length === 0 && offset === 0) {
                    setShowIngestLink(true);
                    setEpisodeData([]);
                    return;
                }
                
                if (data.episodes) {
                    if (append) {
                        setEpisodeData(prev => [...prev, ...data.episodes]);
                    } else {
                        setEpisodeData(data.episodes);
                    }
                    setPagination(data.pagination);
                } else {
                    // Fallback for old API response format
                    if (append) {
                        setEpisodeData(prev => [...prev, ...data]);
                    } else {
                        setEpisodeData(data);
                    }
                }
                setError(null);
            })
            .catch((err) => {
                setError(err.message);
                if (!append) setEpisodeData([]);
            })
            .finally(() => {
                setLoading(false);
                setLoadingMore(false);
                // Trigger refresh of recently watched section
                setRefreshTrigger(prev => prev + 1);
            });
    }

    function refreshState() {
        setEpisodeData([]);
        setPagination({ total: 0, limit: 50, offset: 0, hasMore: false });
        fetchEpisodes(0, false);
    }

    function loadMoreEpisodes() {
        if (pagination.hasMore && !loadingMore) {
            fetchEpisodes(pagination.offset + pagination.limit, true);
        }
    }

    useEffect(() => { fetchEpisodes(); }, []);

    return (
        <div>
            {showIngestLink && (
                <div className={"bento"}>
                    <h1 id="nothing-found">No shows found</h1>
                    <p>Start by adding some TV shows to your watchlist!</p>
                </div>
            )}
            {loading && (
                <div className={"bento"}>
                    <div>Loading your watchlist...</div>
                </div>
            )}
            {error && (
                <div className={"bento"}>
                    <div>{`There is a problem fetching the post data - ${error}`}</div>
                </div>
            )}
            {episodeData.length > 0 &&
                groupEpisodesBySeries(episodeData).map((seriesData, index) => (
                    <SeriesGroup 
                        key={seriesData.tvdbSeriesId || index} 
                        seriesData={seriesData} 
                        refreshState={refreshState}
                    />
                ))
            }
            {pagination.hasMore && (
                <div className={"bento text-center"}>
                    <button 
                        className="btn btn-outline-primary"
                        onClick={loadMoreEpisodes}
                        disabled={loadingMore}
                    >
                        {loadingMore ? (
                            <>
                                <span className="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Loading more...
                            </>
                        ) : (
                            `Load more episodes (${pagination.total - episodeData.length} remaining)`
                        )}
                    </button>
                </div>
            )}
            <RecentlyWatched 
                refreshTrigger={refreshTrigger} 
                onRefresh={refreshState}
            />
        </div>
    )
}
