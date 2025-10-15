import React, { useState, useEffect } from 'react';
import UnwatchButton from "../atoms/UnwatchButton";
import RemoveButton from "../atoms/RemoveButton";

export default function SeriesGroupRecentlyWatched({ seriesData, refreshState }) {
    const [isExpanded, setIsExpanded] = useState(true);
    const [overview, setOverview] = useState("Loading synopsis...");
    
    const { 
        seriesTitle, 
        tvdbSeriesId, 
        poster, 
        episodes
    } = seriesData;

    const toggleExpanded = () => {
        setIsExpanded(!isExpanded);
    };

    // Fetch series overview from TVDB API
    useEffect(() => {
        if (tvdbSeriesId) {
            fetch(`/api/series/${tvdbSeriesId}/overview`)
                .then(response => response.json())
                .then(data => {
                    setOverview(data.overview || "No synopsis available");
                })
                .catch(() => {
                    setOverview("No synopsis available");
                });
        } else {
            setOverview("No synopsis available");
        }
    }, [tvdbSeriesId]);

    return (
        <div className="bento mb-3" style={{ backgroundColor: '#f8f9fa', borderLeft: '4px solid #28a745' }}>
            {/* Series Header */}
            <div className="d-flex align-items-start gap-3 mb-3">
                {/* Series Poster */}
                <div className="flex-shrink-0">
                    <img 
                        src={poster} 
                        alt={seriesTitle}
                        className="img-fluid"
                        style={{ width: '120px', height: 'auto', borderRadius: '8px', opacity: 0.7 }}
                    />
                </div>
                
                {/* Series Info */}
                <div className="flex-grow-1">
                    <div className="d-flex justify-content-between align-items-start mb-2">
                        <h3 className="mb-1 text-success">{seriesTitle}</h3>
                        <div className="d-flex flex-column gap-2">
                            <button 
                                className="btn btn-sm btn-outline-success"
                                onClick={toggleExpanded}
                                type="button"
                            >
                                {isExpanded ? 'Collapse' : 'Expand'} ({episodes.length} episodes)
                            </button>
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
                    <table className="table table-light table-hover table-sm">
                        <thead className="table-success">
                            <tr>
                                <th scope="col" style={{ width: '80px' }}>Season</th>
                                <th scope="col" style={{ width: '80px' }}>Episode</th>
                                <th scope="col">Title</th>
                                <th scope="col" style={{ width: '140px' }}>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {episodes.map((episode) => (
                                <tr key={episode.id}>
                                    <td className="text-center fw-bold py-2">{episode.season}</td>
                                    <td className="text-center fw-bold py-2">{episode.episode}</td>
                                    <td className="py-2">
                                        <div className="fw-bold text-success">
                                            {episode.title}
                                            <span className="text-success ms-2">✓ Watched</span>
                                        </div>
                                    </td>
                                    <td className="py-2">
                                        <UnwatchButton 
                                            id={episode.id} 
                                            refreshState={refreshState}
                                            size="sm"
                                        />
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </div>
    );
}