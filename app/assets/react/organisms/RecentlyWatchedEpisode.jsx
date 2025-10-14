import React from 'react';
import UnwatchButton from "../atoms/UnwatchButton";
import RemoveButton from "../atoms/RemoveButton";

export default function RecentlyWatchedEpisode({episodeData, refreshState}) {
    const mobileStyles = {
        card: {
            marginBottom: '1rem', 
            backgroundColor: '#f8f9fa', 
            borderLeft: '4px solid #28a745'
        },
        desktopLayout: {
            display: 'flex', 
            alignItems: 'flex-start', 
            gap: '1rem'
        },
        mobileLayout: {
            display: 'block'
        },
        imageContainer: {
            flex: '0 0 auto', 
            width: '80px',
            marginBottom: '1rem'
        },
        imageContainerMobile: {
            width: '80px',
            marginBottom: '1rem',
            float: 'left',
            marginRight: '1rem'
        },
        image: {
            width: '100%', 
            height: 'auto', 
            borderRadius: '4px', 
            opacity: 0.7
        },
        contentContainer: {
            flex: '1'
        },
        contentContainerMobile: {
            overflow: 'hidden' // Creates block formatting context for proper float clearing
        },
        titleSection: {
            marginBottom: '0.5rem'
        },
        seriesTitle: {
            margin: '0', 
            fontSize: '1.25rem', 
            color: '#28a745'
        },
        episodeTitle: {
            margin: '0', 
            fontSize: '1rem', 
            color: '#666'
        },
        episodeInfo: {
            margin: '0', 
            fontSize: '0.875rem', 
            color: '#888'
        },
        watchedIndicator: {
            color: '#28a745', 
            fontWeight: 'bold'
        },
        description: {
            margin: '0', 
            fontSize: '0.875rem', 
            lineHeight: '1.4', 
            color: '#666'
        },
        buttonContainer: {
            flex: '0 0 auto', 
            display: 'flex', 
            flexDirection: 'column', 
            gap: '0.5rem', 
            minWidth: '120px'
        },
        buttonContainerMobile: {
            display: 'flex', 
            gap: '0.5rem', 
            marginTop: '1rem',
            width: '160px' // Wider to accommodate both buttons properly
        },
        buttonContainerMobileButton: {
            flex: '1',
            fontSize: '0.75rem',
            padding: '0.375rem 0.25rem', // Reduced padding for smaller buttons
            minWidth: '75px' // Ensure minimum width for readability
        }
    };

    // Simple mobile detection based on screen width
    const [isMobile, setIsMobile] = React.useState(window.innerWidth <= 768);

    React.useEffect(() => {
        const handleResize = () => setIsMobile(window.innerWidth <= 768);
        window.addEventListener('resize', handleResize);
        return () => window.removeEventListener('resize', handleResize);
    }, []);

    if (isMobile) {
        return (
            <div key={episodeData.id} className="episode-card bento" style={mobileStyles.card}>
                <div style={mobileStyles.mobileLayout}>
                    <div style={mobileStyles.imageContainerMobile}>
                        <img src={episodeData.poster} 
                             alt={episodeData.seriesTitle} 
                             style={mobileStyles.image} />
                    </div>
                    <div style={mobileStyles.contentContainerMobile}>
                        <div style={mobileStyles.titleSection}>
                            <h3 style={mobileStyles.seriesTitle}>{episodeData.seriesTitle}</h3>
                            <h4 style={mobileStyles.episodeTitle}>{episodeData.title}</h4>
                            <p style={mobileStyles.episodeInfo}>
                                Season {episodeData.season}, Episode {episodeData.episode}
                                {episodeData.airDate && (
                                    <> • {new Date(episodeData.airDate.date || episodeData.airDate).toLocaleDateString()}</>
                                )}
                                <span style={mobileStyles.watchedIndicator}> • Watched ✓</span>
                            </p>
                        </div>
                        <p style={mobileStyles.description}>{episodeData.description}</p>
                        <div className="d-flex gap-2 mt-2">
                            <UnwatchButton 
                                id={episodeData.id} 
                                refreshState={refreshState}
                            />
                            <RemoveButton 
                                id={episodeData.tvdbSeriesId} 
                                refreshState={refreshState}
                            />
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    // Desktop layout (unchanged)
    return (
        <div key={episodeData.id} className="episode-card bento" style={mobileStyles.card}>
            <div style={mobileStyles.desktopLayout}>
                <div style={mobileStyles.imageContainer}>
                    <img src={episodeData.poster} 
                         alt={episodeData.seriesTitle} 
                         style={mobileStyles.image} />
                </div>
                <div style={mobileStyles.contentContainer}>
                    <div style={mobileStyles.titleSection}>
                        <h3 style={mobileStyles.seriesTitle}>{episodeData.seriesTitle}</h3>
                        <h4 style={mobileStyles.episodeTitle}>{episodeData.title}</h4>
                        <p style={mobileStyles.episodeInfo}>
                            Season {episodeData.season}, Episode {episodeData.episode}
                            {episodeData.airDate && (
                                <> • {new Date(episodeData.airDate.date || episodeData.airDate).toLocaleDateString()}</>
                            )}
                            <span style={mobileStyles.watchedIndicator}> • Watched ✓</span>
                        </p>
                    </div>
                    <p style={mobileStyles.description}>{episodeData.description}</p>
                </div>
                <div className="d-flex flex-column gap-2">
                    <UnwatchButton id={episodeData.id} refreshState={refreshState}/>
                    <RemoveButton id={episodeData.tvdbSeriesId} refreshState={refreshState}/>
                </div>
            </div>
        </div>
    )
}