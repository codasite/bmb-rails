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
import { PlayStorage } from '../../shared/storages/PlayStorage'
import SubmitPicksRegisterModal from './SubmitPicksRegisterModal'
import StripePaymentModal from './StripePaymentModal'

interface PlayPageProps {
  bracketProductArchiveUrl: string
  myPlayHistoryUrl: string
  isUserLoggedIn: boolean
  bracketStylesheetUrl: string
  bracket?: BracketRes
  matchTree?: MatchTree
  setMatchTree?: (matchTree: MatchTree) => void
  darkMode?: boolean
  setDarkMode?: (darkMode: boolean) => void
  bracketMeta?: BracketMeta
  setBracketMeta?: (bracketMeta: BracketMeta) => void
  userCanPlayPaidBracketForFree?: boolean
}

const PlayPage = (props: PlayPageProps) => {
  const {
    bracket,
    bracketProductArchiveUrl,
    myPlayHistoryUrl,
    isUserLoggedIn,
    matchTree,
    setMatchTree,
    bracketMeta,
    setBracketMeta,
    darkMode,
    setDarkMode,
    userCanPlayPaidBracketForFree,
  } = props

  const [processing, setProcessing] = useState(false)
  const [storedPlay, setStoredPlay] = useState<Nullable<PlayReq>>(null)
  const [showRegisterModal, setShowRegisterModal] = useState(false)
  const [showPaymentModal, setShowPaymentModal] = useState(false)
  const [stripeClientSecret, setStripeClientSecret] = useState<string>('')
  const [stripePaymentAmount, setStripePaymentAmount] = useState<number>(null)
  const { width: windowWidth, height: windowHeight } = useContext(
    WindowDimensionsContext
  )
  const showPaginated =
    windowWidth - 100 < getBracketWidth(getNumRounds(bracket?.numTeams))

  const canPrint = bracket?.isPrintable
  const canSubmit = bracket?.isOpen
  const playStorage = new PlayStorage('loadStoredPicks', 'wpbb_play_data_')
  const paymentRequired =
    bracket?.fee > 0 && bracket?.isOpen && !userCanPlayPaidBracketForFree

  useEffect(() => {
    if (!bracket?.id || !bracket?.numTeams || !bracket?.matches) {
      return
    }
    const meta = getBracketMeta(bracket)
    setBracketMeta?.(meta ?? {})
    let tree: Nullable<MatchTree> = null
    const numTeams = bracket.numTeams
    const matches = bracket.matches
    const play = playStorage.loadPlay(bracket.id)
    if (play) {
      tree = MatchTree.fromPicks(numTeams, matches, play.picks)
      setStoredPlay(play)
    } else {
      tree = tree ?? MatchTree.fromMatchRes(numTeams, matches)
    }
    if (tree && setMatchTree) {
      setMatchTree(tree)
    }
  }, [])

  const setMatchTreeAndSaveInStorage = (tree: MatchTree) => {
    setMatchTree(tree)
    playStorage.storePlay(
      {
        bracketId: bracket?.id,
        picks: tree.toMatchPicks(),
        id: playStorage.loadPlay(bracket?.id)?.id,
      },
      bracket?.id
    )
  }

  const getPlayReq = () => {
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
    }
    return playReq
  }

  const handleApparelClick = async () => {
    const playReq = getPlayReq()
    playReq.generateImages = true
    if (
      JSON.stringify(storedPlay?.picks) === JSON.stringify(playReq.picks) &&
      storedPlay?.id
    ) {
      window.location.assign(bracketProductArchiveUrl)
      return
    }
    setProcessing(true)
    return bracketApi
      .createPlay(playReq)
      .then((res) => {
        const playId = res.id
        const newReq = {
          ...playReq,
          id: playId,
        }
        playStorage.storePlay(newReq, bracket?.id)
        window.location.assign(bracketProductArchiveUrl)
      })
      .catch((err) => {
        console.error('error: ', err)
        setProcessing(false)
        Sentry.captureException(err)
      })
  }

  const handleSubmitPicksClick = async () => {
    const playReq = getPlayReq()
    playReq.generateImages = false
    playReq.createStripePaymentIntent = paymentRequired
    if (
      JSON.stringify(storedPlay?.picks) === JSON.stringify(playReq.picks) &&
      storedPlay.id
    ) {
      if (!paymentRequired) {
        if (isUserLoggedIn) {
          window.location.assign(myPlayHistoryUrl)
        } else {
          setShowRegisterModal(true)
        }
        return
      }
      if (stripeClientSecret) {
        setShowPaymentModal(true)
        return
      }
      await bracketApi
        .createStripePaymentIntent({ playId: storedPlay.id })
        .then((res) => {
          setStripeClientSecret(res.clientSecret)
          setStripePaymentAmount(res.amount)
          setShowPaymentModal(true)
        })
        .catch((err) => {
          console.error('error: ', err)
          Sentry.captureException(err)
        })

      return
    }
    setProcessing(true)
    return bracketApi
      .createPlay(playReq)
      .then((res) => {
        const playId = res.id
        const newReq = {
          ...playReq,
          id: playId,
        }
        playStorage.storePlay(newReq, bracket?.id)
        setStoredPlay(newReq)
        if (paymentRequired) {
          setStripeClientSecret(res.stripePaymentIntentClientSecret)
          setStripePaymentAmount(res.stripePaymentAmount)
          setShowPaymentModal(true)
          setProcessing(false)
        } else if (isUserLoggedIn) {
          window.location.assign(myPlayHistoryUrl)
        } else {
          setShowRegisterModal(true)
        }
      })
      .catch((err) => {
        console.error('error: ', err)
        setProcessing(false)
        Sentry.captureException(err)
      })
  }

  const playBuilderProps = {
    matchTree,
    setMatchTree:
      canPrint || canSubmit ? setMatchTreeAndSaveInStorage : undefined,
    handleApparelClick: canPrint ? handleApparelClick : undefined,
    handleSubmitPicksClick: canSubmit ? handleSubmitPicksClick : undefined,
    canPlay: canPrint || canSubmit,
    processing,
    darkMode,
    setDarkMode,
    bracketMeta,
    setBracketMeta,
  }

  return (
    <>
      <SubmitPicksRegisterModal
        show={showRegisterModal}
        setShow={setShowRegisterModal}
      />
      <StripePaymentModal
        title={'Submit Your Picks'}
        show={showPaymentModal}
        setShow={setShowPaymentModal}
        clientSecret={stripeClientSecret}
        paymentAmount={stripePaymentAmount}
        myPlayHistoryUrl={myPlayHistoryUrl}
      />
      {showPaginated ? (
        <PaginatedPlayBuilder {...playBuilderProps} />
      ) : (
        <PlayBuilder {...playBuilderProps} />
      )}
    </>
  )
}

const WrappedPlayBuilderPage = WithWindowDimensions(
  WithDarkMode(WithMatchTree(WithBracketMeta(PlayPage)))
)

export default WrappedPlayBuilderPage
