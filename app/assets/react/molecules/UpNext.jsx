import React from 'react';
import { useState, useEffect } from "react";
import SeriesGroup from "../organisms/SeriesGroup";
import RecentlyWatched from "./RecentlyWatched";

export default function UpNext() {
    const [episodeData, setEpisodeData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [showIngestLink, setShowIngestLink] = useState(false);
    const [refreshTrigger, setRefreshTrigger] = useState(0);

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

    function fetchEpisodes() {
        setLoading(true);
        
        const startTime = performance.now();
        console.log('🚀 Starting to fetch episodes...');
        
        fetch('/api/nextup', {
            method: "GET",
            headers: {
                "Content-Type": "application/json"
            }
        })
            .then((response) => {
                const apiResponseTime = performance.now();
                console.log(`📡 API Response received in ${(apiResponseTime - startTime).toFixed(2)}ms`);
                
                if(!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then((data) => {
                const jsonParseTime = performance.now();
                console.log(`📄 JSON parsed in ${(jsonParseTime - startTime).toFixed(2)}ms`);
                
                if(data.episodes && data.episodes.length === 0) {
                    setShowIngestLink(true);
                    setEpisodeData([]);
                    return;
                }
                
                let episodeCount = 0;
                if (data.episodes) {
                    setEpisodeData(data.episodes);
                    episodeCount = data.episodes.length;
                } else {
                    // Fallback for old API response format
                    setEpisodeData(data);
                    episodeCount = data.length;
                }
                
                console.log(`📺 Loaded ${episodeCount} episodes`);
                setError(null);
            })
            .catch((err) => {
                setError(err.message);
                setEpisodeData([]);
                console.error('❌ Error fetching episodes:', err.message);
            })
            .finally(() => {
                const totalTime = performance.now();
                console.log(`✅ Total fetch time: ${(totalTime - startTime).toFixed(2)}ms`);
                
                setLoading(false);
                // Trigger refresh of recently watched section
                setRefreshTrigger(prev => prev + 1);
            });
    }

    function refreshState() {
        setEpisodeData([]);
        fetchEpisodes();
    }

    useEffect(() => { 
        const pageStartTime = performance.now();
        console.log('🏠 Homepage component mounting...');
        
        fetchEpisodes();
        
        // Measure render completion
        const timeoutId = setTimeout(() => {
            const pageEndTime = performance.now();
            console.log(`🎯 Homepage fully rendered in ${(pageEndTime - pageStartTime).toFixed(2)}ms`);
        }, 0);
        
        return () => clearTimeout(timeoutId);
    }, []);

    // Measure when episodes are actually rendered
    useEffect(() => {
        if (episodeData.length > 0 && !loading) {
            const renderTime = performance.now();
            console.log(`🖼️  Episodes rendered to DOM - Total episodes: ${episodeData.length}`);
            console.log(`📊 Series groups created: ${groupEpisodesBySeries(episodeData).length}`);
        }
    }, [episodeData, loading]);

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
            <RecentlyWatched 
                refreshTrigger={refreshTrigger} 
                onRefresh={refreshState}
            />
        </div>
    )
}
