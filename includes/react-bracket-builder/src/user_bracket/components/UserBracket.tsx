import React, { useState, useEffect } from 'react';
import { Button } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { bracketApi } from '../../api/bracketApi';
import { Nullable } from '../../utils/types';
import { Bracket } from '../../bracket/components/Bracket';
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

	console.log('in UserBracket')
	console.log('bracket', bracketRes)

	return (
		<div>
			HI!!!
		</div>
	)
}

export default UserBracket;