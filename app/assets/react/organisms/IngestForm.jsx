import React, {useState, useEffect} from 'react'

export default function IngestForm({id}) {

    const [ingestState, setIngestState ] = useState("Start");
    const [ingestDisabled, setIngestDisabled ] = useState('');
    const [ingestSeason, setIngestSeason ] = useState(1);
    const [ingestEpisode, setIngestEpisode ] = useState(1);
    const [tvdbNetwork, setTvdbNetwork ] = useState(null);
    const [networkLoading, setNetworkLoading ] = useState(true);

    // Fetch network information from TVDB
    useEffect(() => {
        fetch(`/api/series/${id}/network`)
            .then(response => response.json())
            .then(data => {
                setTvdbNetwork(data.network);
                setNetworkLoading(false);
            })
            .catch(error => {
                console.log('Error fetching network data:', error);
                setNetworkLoading(false);
            });
    }, [id]);



    function ingestShow(id) {
        console.log("Ingesting show " + id + " season " + ingestSeason + " episode " + ingestEpisode);
        setIngestState('Ingesting...');
        fetch('/api/tvdb/series/ingest',{
            method: "POST",
            headers: {
                "Content-Type": "application/json+ld"
            },
            body: JSON.stringify({
                seriesId: id,
                season: Number(ingestSeason),
                episode: Number(ingestEpisode)
            })
        })
        .then((response) => {
            if(!response.ok) {
                throw new Error("Network response was not ok");
            }
            return response.json();
        })
        .then((ingestData) => {
            console.log("Ingested show " + ingestData);
            setIngestState("Ingested");
            setIngestDisabled("disabled");
        })
        .catch((err) => {
            console.log("Error ingesting show " + err.message);
            setIngestState("Error during Ingest");
            setIngestDisabled("disabled");
        })
    }

    return (
        <div className="ingestForm">
            <div className="partialIngest">
                <div className="partialIngestInput">
                    <label htmlFor={id + "season"}>Season: </label>
                    <input name={id + "season"} type={"number"} placeholder={"1"} id={"season"} onChange={(e) => setIngestSeason(e.target.value)}></input>
                </div>
                <div className="partialIngestInput">
                    <label htmlFor={id + "season"}>Episode: </label>
                    <input name={id + "episode"} type={"number"} placeholder={"1"} id={"episode"} onChange={(e) => setIngestEpisode(e.target.value)}></input>
                </div>
            </div>
            {tvdbNetwork && (
                <div className="network-info mb-3">
                    <div className="badge bg-primary">
                        Original Network: {tvdbNetwork}
                    </div>
                </div>
            )}
            <button className={"btn btn-lg btn-block btn-dark " + ingestDisabled} type="button" onClick={() => ingestShow(id)}>
                {ingestState}
            </button>
        </div>
    )
}
