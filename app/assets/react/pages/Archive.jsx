import React, { useState, useEffect } from 'react';

export default function Archive() {
    const [archivedSeries, setArchivedSeries] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [seriesNetworks, setSeriesNetworks] = useState({});

    function fetchArchivedSeries() {
        setLoading(true);
        
        fetch('/api/archive', {
            method: "GET",
            headers: {
                "Content-Type": "application/json"
            }
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then((data) => {
                const series = data.archivedSeries || [];
                setArchivedSeries(series);
                setError(null);
                
                // Fetch network information for each series
                series.forEach(seriesItem => {
                    if (seriesItem.tvdbSeriesId) {
                        fetchSeriesNetwork(seriesItem.tvdbSeriesId);
                    }
                });
            })
            .catch((err) => {
                setError(err.message);
                setArchivedSeries([]);
            })
            .finally(() => {
                setLoading(false);
            });
    }

    function fetchSeriesNetwork(tvdbSeriesId) {
        fetch(`/api/series/${tvdbSeriesId}/network`)
            .then(response => response.json())
            .then(data => {
                setSeriesNetworks(prev => ({
                    ...prev,
                    [tvdbSeriesId]: data.network
                }));
            })
            .catch(() => {
                // Ignore network fetch errors
            });
    }

    function restoreSeries(tvdbSeriesId, seriesTitle) {
        if (!window.confirm(`Are you sure you want to restore "${seriesTitle}" to your watchlist?`)) {
            return;
        }

        fetch(`/api/archive/${tvdbSeriesId}/restore`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            }
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Failed to restore series");
                }
                return response.json();
            })
            .then(() => {
                // Remove from archived list
                setArchivedSeries(prev => prev.filter(series => series.tvdbSeriesId !== tvdbSeriesId));
            })
            .catch((err) => {
                alert(`Error restoring series: ${err.message}`);
            });
    }

    function permanentlyDeleteSeries(tvdbSeriesId, seriesTitle) {
        if (!window.confirm(`Are you sure you want to PERMANENTLY delete "${seriesTitle}"? This action cannot be undone.`)) {
            return;
        }

        fetch(`/api/archive/${tvdbSeriesId}`, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json"
            }
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Failed to delete series");
                }
                return response.json();
            })
            .then(() => {
                // Remove from archived list
                setArchivedSeries(prev => prev.filter(series => series.tvdbSeriesId !== tvdbSeriesId));
            })
            .catch((err) => {
                alert(`Error deleting series: ${err.message}`);
            });
    }

    useEffect(() => {
        fetchArchivedSeries();
    }, []);

    return (
        <div>
            {loading && (
                <div className="bento">
                    <div>Loading archived shows...</div>
                </div>
            )}

            {error && (
                <div className="bento">
                    <div className="text-danger">Error loading archived shows: {error}</div>
                </div>
            )}

            {!loading && archivedSeries.length === 0 && (
                <div className="bento text-center">
                    <h1 id="nothing-found">No Archived Shows</h1>
                    <p>Shows you remove from your watchlist will appear here.</p>
                </div>
            )}

            {archivedSeries.length > 0 && (
                <>
                    {archivedSeries.map((series) => (
                        <div key={series.tvdbSeriesId} className="bento mb-3 series-group-archive">
                            {/* Series Header */}
                            <div className="d-flex align-items-start gap-3 mb-3">
                                {/* Series Poster */}
                                <div className="flex-shrink-0">
                                    <img 
                                        src={series.poster} 
                                        alt={series.seriesTitle}
                                        className="img-fluid series-poster"
                                        onError={(e) => {
                                            e.target.onerror = null; // Prevent infinite loop
                                            e.target.src = '/build/images/fallback-image.png';
                                        }}
                                    />
                                </div>
                                
                                {/* Series Info */}
                                <div className="flex-grow-1">
                                    <div className="d-flex justify-content-between align-items-start mb-2">
                                        <div className="flex-grow-1">
                                            <h3 className="mb-1 text-light">{series.seriesTitle}</h3>
                                            
                                            <div className="mb-2">
                                                <small className="text-light me-3">
                                                    📅 Archived: {new Date(series.archivedAt).toLocaleDateString()}
                                                </small>
                                            </div>

                                            <div className="mb-2">
                                                {seriesNetworks[series.tvdbSeriesId] && (
                                                    <span className="badge bg-primary me-2">{seriesNetworks[series.tvdbSeriesId]}</span>
                                                )}

                                                {series.universe && (
                                                    <span className="badge bg-info me-2">{series.universe}</span>
                                                )}
                                            </div>

                                            <div className="mb-2">
                                                <small className="text-light">
                                                    Progress: {series.watchedEpisodes}/{series.totalEpisodes} episodes watched
                                                </small>
                                                <div className="progress mt-1" style={{height: '6px'}}>
                                                    <div 
                                                        className="progress-bar bg-success" 
                                                        role="progressbar" 
                                                        style={{width: `${series.totalEpisodes > 0 ? (series.watchedEpisodes / series.totalEpisodes) * 100 : 0}%`}}
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div className="d-flex flex-column gap-2">
                                            <button 
                                                className="btn btn-sm btn-success"
                                                onClick={() => restoreSeries(series.tvdbSeriesId, series.seriesTitle)}
                                            >
                                                🔄 Restore
                                            </button>
                                            <button 
                                                className="btn btn-sm btn-outline-danger"
                                                onClick={() => permanentlyDeleteSeries(series.tvdbSeriesId, series.seriesTitle)}
                                            >
                                                🗑️ Delete
                                            </button>
                                        </div>
                                    </div>
                                    
                                    {series.overview && (
                                        <p className="text-light mb-0 small">{series.overview}</p>
                                    )}
                                </div>
                            </div>
                        </div>
                    ))}
                </>
            )}
        </div>
    );
}