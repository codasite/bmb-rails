import React, { useEffect, useState, useContext } from 'react'
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
import { useWindowDimensions } from '../../../utils/hooks'
import { PaginatedPlayBuilder } from '../PaginatedPlayBuilder/PaginatedPlayBuilder'
import { PlayBuilder } from './PlayBuilder'
import {
  getBracketMeta,
  getBracketWidth,
} from '../../shared/components/Bracket/utils'
import { getNumRounds } from '../../shared/models/operations/GetNumRounds'
import { WithWindowDimensions } from '../../shared/components/HigherOrder/WithWindowDimensions'
import { WindowDimensionsContext } from '../../shared/context/WindowDimensionsContext'

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
  console.log('redirectUrl', redirectUrl)

  const [processing, setProcessing] = useState(false)
  const { width: windowWidth, height: windowHeight } = useContext(
    WindowDimensionsContext
  )
  const showPaginated =
    windowWidth - 100 < getBracketWidth(getNumRounds(bracket?.numTeams))

  useEffect(() => {
    let tree: Nullable<MatchTree> = null
    if (bracket) {
      const numTeams = bracket.numTeams
      const matches = bracket.matches
      tree = MatchTree.fromMatchRes(numTeams, matches)
      const meta = getBracketMeta(bracket)
      setBracketMeta?.(meta)
    }
    if (tree && setMatchTree) {
      setMatchTree(tree)
    }
  }, [])

  const handleApparelClick = () => {
    const picks = matchTree?.toMatchPicks()
    const bracketId = bracket?.id
    if (!picks) {
      const msg = 'Cannot create play. Missing picks'
      console.error(msg)
      Sentry.captureException(msg)
      return
    }
    const playReq: PlayReq = {
      title: bracket?.title,
      bracketId: bracket?.id,
      picks: picks,
      generateImages: true,
    }

    setProcessing(true)
    bracketApi
      .createPlay(playReq)
      .then((res) => {
        window.location.href = redirectUrl
      })
      .catch((err) => {
        console.error('error: ', err)
        Sentry.captureException(err)
      })
      .finally(() => {
        setProcessing(false)
        console.timeEnd('createPlay')
        console.log('createPlay')
      })
  }

  const playBuilderProps = {
    matchTree,
    setMatchTree,
    handleApparelClick,
    processing,
    darkMode,
    setDarkMode,
    bracketMeta,
    setBracketMeta,
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
