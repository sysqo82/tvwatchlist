import React, { useState, useEffect } from 'react';
import UnwatchButton from "../atoms/UnwatchButton";
import RemoveButton from "../atoms/RemoveButton";
import RefreshButton from "../atoms/RefreshButton";

export default function SeriesGroupRecentlyWatched({ seriesData, refreshState }) {
    const [isExpanded, setIsExpanded] = useState(false);
    const [overview, setOverview] = useState("Loading synopsis...");
    const [network, setNetwork] = useState(null);
    const [networkLoading, setNetworkLoading] = useState(true);
    const [imageError, setImageError] = useState(false);
    
    const { 
        seriesTitle, 
        tvdbSeriesId, 
        poster, 
        episodes
    } = seriesData;

    const toggleExpanded = () => {
        setIsExpanded(!isExpanded);
    };

    // Fetch series overview and network from TVDB API
    useEffect(() => {
        if (tvdbSeriesId) {
            // Fetch overview
            fetch(`/api/series/${tvdbSeriesId}/overview`)
                .then(response => response.json())
                .then(data => {
                    setOverview(data.overview || "No synopsis available");
                })
                .catch(() => {
                    setOverview("No synopsis available");
                });

            // Fetch network information
            fetch(`/api/series/${tvdbSeriesId}/network`)
                .then(response => response.json())
                .then(data => {
                    setNetwork(data.network);
                    setNetworkLoading(false);
                })
                .catch(() => {
                    setNetworkLoading(false);
                });
        } else {
            setOverview("No synopsis available");
            setNetworkLoading(false);
        }
    }, [tvdbSeriesId]);

    // Reset image error state when poster changes
    useEffect(() => {
        setImageError(false);
    }, [poster]);

    // Check if refresh is needed (missing poster, image error, or overview)
    const needsRefresh = !poster || poster === '' || imageError || overview === "No synopsis available" || overview === "Loading synopsis...";

    return (
        <div className="bento mb-3 series-group-recently-watched">
            {/* Series Header */}
            <div className="d-flex align-items-start gap-3 mb-3">
                {/* Series Poster */}
                <div className="flex-shrink-0">
                    <img 
                        src={poster} 
                        alt={seriesTitle}
                        className="img-fluid series-poster"
                        onError={(e) => {
                            e.target.onerror = null; // Prevent infinite loop
                            e.target.src = '/build/images/fallback-image.png';
                            setImageError(true); // Mark that image failed to load
                        }}
                    />
                </div>
                
                {/* Series Info */}
                <div className="flex-grow-1">
                    <div className="d-flex justify-content-between align-items-start mb-2">
                        <div className="flex-grow-1">
                            <h3 className="mb-1 text-success">{seriesTitle}</h3>
                            {network && (
                                <div className="mb-2">
                                    <span className="badge bg-success me-2">
                                        {network}
                                    </span>
                                </div>
                            )}
                        </div>
                        <div className="d-flex flex-column gap-2">
                            <button 
                                className="btn btn-sm btn-outline-success"
                                onClick={toggleExpanded}
                                type="button"
                            >
                                {isExpanded ? 'Collapse' : 'Expand'} ({episodes.length} episodes)
                            </button>
                            {needsRefresh && (
                                <RefreshButton 
                                    tvdbSeriesId={tvdbSeriesId}
                                    refreshState={refreshState}
                                    size="sm"
                                    variant="outline-info"
                                    className="w-100"
                                />
                            )}
                            <RemoveButton 
                                id={tvdbSeriesId} 
                                refreshState={refreshState}
                                size="sm"
                                variant="outline-danger"
                                className="w-100"
                            />
                        </div>
                    </div>
                    <p className="text-muted mb-0 small">{overview}</p>
                </div>
            </div>

            {/* Episodes Table */}
            {isExpanded && (
                <div className="table-responsive">
                    <table className="table table-light table-hover table-sm series-table">
                        <thead className="table-success">
                            <tr>
                                <th scope="col" className="col-season text-center">S</th>
                                <th scope="col" className="col-episode text-center">Ep</th>
                                <th scope="col" className="col-title">Title</th>
                                <th scope="col" className="col-actions text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {episodes.map((episode) => {
                                // Check if title is generic like "Episode 1", "Episode 2", etc.
                                const isGenericTitle = /^Episode \d+$/i.test(episode.title);
                                const displayTitle = isGenericTitle && episode.description 
                                    ? episode.description 
                                    : episode.title;
                                
                                return (
                                    <tr key={episode.id}>
                                        <td className="text-center fw-bold py-2">{episode.season}</td>
                                        <td className="text-center fw-bold py-2">{episode.episode}</td>
                                        <td className="py-2">
                                            <div className="fw-bold text-success">
                                                {displayTitle}
                                                <span className="text-success ms-2">✓ Watched</span>
                                            </div>
                                            {isGenericTitle && episode.description && (
                                                <small className="text-muted d-block mt-1">
                                                    (Generic title replaced with synopsis)
                                                </small>
                                            )}
                                        </td>
                                        <td className="py-2">
                                            <UnwatchButton 
                                                id={episode.id} 
                                                refreshState={refreshState}
                                                size="sm"
                                            />
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            )}
        </div>
    );
}