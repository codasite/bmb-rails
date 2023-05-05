import React, { useState, useEffect } from 'react';
import { Button } from 'react-bootstrap';
import { Modal } from 'react-bootstrap';
import { bracketApi } from '../../api/bracketApi';
import { Nullable } from '../../utils/types';
import { Bracket } from '../../bracket/components/Bracket';
import { MatchTree, WildcardPlacement } from '../../bracket/models/MatchTree';
import { BracketRes } from '../../api/types/bracket';

// const UserBracketProps = {


// export const UserBracket = (props) => {

// }