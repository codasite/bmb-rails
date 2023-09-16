import React, { useState, useEffect, createContext, useContext } from 'react';
import { WithMatchTree } from '../shared/components/WithMatchTree';

import { MatchTree } from '../shared/models/MatchTree';

const PlayTournamentBuilder = (props: any) => {
	const { matchTree, setMatchTree } = props;
	return (
		<div className='wpbb-play-tournament-builder'>
			<h1>Play Tournament Builder</h1>
		</div>
	)
}
