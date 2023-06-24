import React, { useState, useEffect, useRef, forwardRef } from 'react';
import { Button, InputGroup } from 'react-bootstrap';
import { Form } from 'react-bootstrap';
import { Nullable } from '../../utils/types';
import { MatchTree, Round, MatchNode, Team } from '../models/MatchTree';
import LineTo, { SteppedLineTo, Line } from 'react-lineto';
//@ts-ignore
import { ReactComponent as BracketLogo } from '../../assets/BMB-ICON-CURRENT.svg';