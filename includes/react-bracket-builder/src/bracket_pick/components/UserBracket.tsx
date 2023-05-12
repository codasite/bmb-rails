import React, { useState, useEffect } from 'react';
import { Button } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { bracketApi } from '../../api/bracketApi';
import { Nullable } from '../../utils/types';
// import { Bracket } from '../../bracket/components/Bracket';
// import { Bracket } from '../../bracket/components/Bracket';
import { PairedBracket } from '../../bracket/components/PairedBracket';

import { MatchTree, WildcardPlacement } from '../../bracket/models/MatchTree';
import { BracketRes } from '../../api/types/bracket';


interface UserBracketProps {
	bracketId?: number;
	bracketRes?: BracketRes;
}



const UserBracket = (props) => {
	const {
		bracketId,
		bracketRes
	} = props;

	const [matchTree, setMatchTree] = useState<Nullable<MatchTree>>(null);

	useEffect(() => {
		if (bracketId) {
			bracketApi.getBracket(bracketId).then((res) => {
				setMatchTree(MatchTree.fromRounds(res.rounds));
			});
		} else if (bracketRes) {
			setMatchTree(MatchTree.fromRounds(bracketRes.rounds));
		}
	}, [bracketId, bracketRes]);

	return (
		<div>
			{matchTree ? <PairedBracket matchTree={matchTree} setMatchTree={setMatchTree} canPick /> : 'Loading...'}
			{/* {matchTree ? <Bracket matchTree={matchTree} setMatchTree={setMatchTree} canPick /> : 'Loading...'} */}
		</div>
	)
}


export default UserBracket;