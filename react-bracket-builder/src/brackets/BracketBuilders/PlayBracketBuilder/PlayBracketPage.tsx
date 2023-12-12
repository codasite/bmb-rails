import React, { useContext, useEffect, useState } from 'react'
import * as Sentry from '@sentry/react'
import { bracketApi } from '../../shared/api/bracketApi'
import { Nullable } from '../../../utils/types'
import { MatchTree } from '../../shared/models/MatchTree'
import { BracketMeta } from '../../shared/context/context'
import {
  WithBracketMeta,
  WithDarkMode,
  WithMatchTree,
  WithProvider,
} from '../../shared/components/HigherOrder'
import { BracketRes, PlayReq } from '../../shared/api/types/bracket'
import { PaginatedPlayBuilder } from '../PaginatedPlayBuilder/PaginatedPlayBuilder'
import { PlayBuilder } from './PlayBuilder'
import {
  getBracketMeta,
  getBracketWidth,
} from '../../shared/components/Bracket/utils'
import { getNumRounds } from '../../shared/models/operations/GetNumRounds'
import { WithWindowDimensions } from '../../shared/components/HigherOrder/WithWindowDimensions'
import { WindowDimensionsContext } from '../../shared/context/WindowDimensionsContext'
import { MatchTreeStorage } from './MatchTreeStorage'

interface PlayPageProps {
  redirectUrl: string
  bracketStylesheetUrl: string
  bracket?: BracketRes
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  darkMode?: boolean
  setDarkMode?: (darkMode: boolean) => void
  bracketMeta?: BracketMeta
  setBracketMeta?: (bracketMeta: BracketMeta) => void
}

const PlayPage = (props: PlayPageProps) => {
  const {
    bracket,
    redirectUrl,
    bracketStylesheetUrl,
    matchTree,
    setMatchTree,
    bracketMeta,
    setBracketMeta,
    darkMode,
    setDarkMode,
  } = props

  const [processing, setProcessing] = useState(false)
  const { width: windowWidth, height: windowHeight } = useContext(
    WindowDimensionsContext
  )
  const showPaginated =
    windowWidth - 100 < getBracketWidth(getNumRounds(bracket?.numTeams))

  const canPlay = bracket?.status !== 'upcoming'
  const matchTreeStorage = new MatchTreeStorage(
    'loadStoredPicks',
    'wpbb_play_data_'
  )

  useEffect(() => {
    let tree: Nullable<MatchTree> = matchTreeStorage.loadMatchTree(bracket?.id)
    if (bracket) {
      const numTeams = bracket.numTeams
      const matches = bracket.matches
      tree = tree ?? MatchTree.fromMatchRes(numTeams, matches)
      const meta = getBracketMeta(bracket)
      setBracketMeta?.(meta)
    }
    if (tree && setMatchTree) {
      setMatchTree(tree)
    }
  }, [])

  const setMatchTreeAndSaveInStorage = (tree: MatchTree) => {
    setMatchTree(tree)
    matchTreeStorage.storeMatchTree(tree, bracket?.id)
  }

  const handleApparelClick = () => {
    const picks = matchTree?.toMatchPicks()
    const bracketId = bracket?.id
    if (!picks || !bracketId) {
      const msg = 'Cannot create play. Missing picks'
      console.error(msg)
      Sentry.captureException(msg)
      return
    }
    const playReq: PlayReq = {
      title: bracket?.title,
      bracketId: bracketId,
      picks: picks,
      generateImages: true,
    }

    setProcessing(true)
    bracketApi
      .createPlay(playReq)
      .then((res) => {
        window.location.assign(redirectUrl)
      })
      .catch((err) => {
        console.error('error: ', err)
        setProcessing(false)
        Sentry.captureException(err)
      })
      .finally(() => {
        console.timeEnd('createPlay')
        console.log('createPlay')
      })
  }

  const playBuilderProps = {
    matchTree,
    setMatchTree: canPlay ? setMatchTreeAndSaveInStorage : undefined,
    handleApparelClick,
    processing,
    darkMode,
    setDarkMode,
    bracketMeta,
    setBracketMeta,
    canPlay,
  }

  if (showPaginated) {
    return <PaginatedPlayBuilder {...playBuilderProps} />
  }

  return <PlayBuilder {...playBuilderProps} />
}

const WrappedPlayPage = WithWindowDimensions(
  WithProvider(WithDarkMode(WithMatchTree(WithBracketMeta(PlayPage))))
)

export default WrappedPlayPage
