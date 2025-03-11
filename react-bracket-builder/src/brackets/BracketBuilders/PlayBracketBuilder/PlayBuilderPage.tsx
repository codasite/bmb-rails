import { useContext, useEffect, useState } from 'react'
import { bracketApi } from '../../shared/api/bracketApi'
import { Nullable } from '../../../utils/types'
import { MatchTree } from '../../shared/models/MatchTree'
import {
  BracketMeta,
  BracketMetaContext,
  MatchTreeContext,
} from '../../shared/context/context'
import { BracketRes, PlayReq, PlayRes } from '../../shared/api/types/bracket'
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
import { logger } from '../../../utils/Logger'
import mergePicksFromPlayAndResults from '../../../features/VotingBracket/mergePicksFromPlayAndResults'
import { WithBracketMeta } from '../../shared/components/HigherOrder/WithBracketMeta'

const PlayBuilderPage = (props: {
  // for testing
  matchTree?: MatchTree
  bracketProductArchiveUrl: string
  myPlayHistoryUrl: string
  isUserLoggedIn: boolean
  bracket?: BracketRes
  play?: PlayRes
  userCanPlayBracketForFree?: boolean
  loginUrl: string
}) => {
  const {
    bracket,
    bracketProductArchiveUrl,
    myPlayHistoryUrl,
    isUserLoggedIn,
    userCanPlayBracketForFree,
    play,
  } = props

  let tree: MatchTree
  if (props.matchTree) {
    tree = props.matchTree
  } else if (bracket.isVoting) {
    tree = MatchTree.fromPicks(
      bracket,
      mergePicksFromPlayAndResults(
        bracket.results || [],
        play?.picks,
        bracket.liveRoundIndex
      )
    )
  } else if (play) {
    tree = MatchTree.fromPicks(bracket, play.picks)
  } else {
    tree = MatchTree.fromMatchRes(bracket)
  }
  const { bracketMeta, setBracketMeta } = useContext(BracketMetaContext)
  useEffect(() => {
    setBracketMeta(getBracketMeta(bracket))
  }, [bracket, setBracketMeta])

  const [matchTree, setMatchTree] = useState<MatchTree>(tree)
  const [processingAddToApparel, setProcessingAddToApparel] = useState(false)
  const [addToApparelError, setAddToApparelError] = useState(false)
  const [submitPicksError, setSubmitPicksError] = useState(false)
  const [processingSubmitPicks, setProcessingSubmitPicks] = useState(false)
  const [storedPlay, setStoredPlay] = useState<Nullable<PlayReq>>(null)
  const [showRegisterModal, setShowRegisterModal] = useState(!isUserLoggedIn)
  const [showPaymentModal, setShowPaymentModal] = useState(false)
  const [stripeClientSecret, setStripeClientSecret] = useState<string>('')
  const [stripePaymentAmount, setStripePaymentAmount] = useState<number>(null)
  const { width: windowWidth } = useContext(WindowDimensionsContext)

  const showPaginated =
    windowWidth - 100 < getBracketWidth(getNumRounds(bracket?.numTeams))

  const canPrint = bracket?.isPrintable
  const canSubmit = bracket?.isOpen && props.isUserLoggedIn
  const playStorage = new PlayStorage('loadStoredPicks', 'wpbb_play_data_')
  const paymentRequired = !userCanPlayBracketForFree

  useEffect(() => {
    const stored = playStorage.loadPlay(bracket?.id)
    if (stored) {
      const tree = MatchTree.fromPicks(bracket, stored.picks)
      setMatchTreeAndSaveInStorage(tree)
    }
  }, [])

  const clearError = () => {
    setAddToApparelError(false)
    setSubmitPicksError(false)
  }

  const setMatchTreeAndSaveInStorage = (tree: MatchTree) => {
    setMatchTree(tree.clone())
    playStorage.storePlay(
      {
        bracketId: bracket?.id,
        picks: tree.toMatchPicks(),
        id: playStorage.loadPlay(bracket?.id)?.id,
      },
      bracket?.id
    )
  }

  const playExists = (playReq: PlayReq) => {
    return (
      storedPlay?.id &&
      JSON.stringify(storedPlay?.picks) === JSON.stringify(playReq.picks)
    )
  }

  const getPlayReq = () => {
    const picks = matchTree?.toMatchPicks()
    const bracketId = bracket?.id
    if (!picks || !bracketId) {
      const msg = 'Cannot create play. Missing picks'
      logger.error(msg)
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
    clearError()
    setProcessingAddToApparel(true)
    const playReq = getPlayReq()
    playReq.generateImages = true
    try {
      if (playExists(playReq)) {
        // regenerate play images anyway (in case they don't exist)
        await bracketApi.generatePlayImages(storedPlay.id)
      } else {
        const res = await bracketApi.createPlay(playReq)
        const playId = res.id
        const newReq = {
          ...playReq,
          id: playId,
        }
        playStorage.storePlay(newReq, bracket?.id)
      }
      window.location.assign(bracketProductArchiveUrl)
    } catch (err) {
      setProcessingAddToApparel(false)
      setAddToApparelError(true)
      logger.error(err)
    }
  }

  const handleSubmitPicksClick = async () => {
    clearError()
    setProcessingSubmitPicks(true)
    const playReq = getPlayReq()
    playReq.generateImages = false
    playReq.createStripePaymentIntent = paymentRequired
    if (playExists(playReq) && !paymentRequired) {
      window.location.assign(myPlayHistoryUrl)
      return
    }
    if (playExists(playReq) && stripeClientSecret) {
      setShowPaymentModal(true)
      setProcessingSubmitPicks(false)
      return
    }
    // if bracket is voting and play id is given, then we can update picks without payment
    if (bracket.isVoting) {
      if (play?.id) {
        await bracketApi.updatePlay(play.id, {
          picks: playReq.picks,
        })
        window.location.assign(myPlayHistoryUrl)
        return
      }
    }

    try {
      if (playExists(playReq)) {
        const res = await bracketApi.createStripePaymentIntent({
          playId: storedPlay.id,
        })
        setStripeClientSecret(res.clientSecret)
        setStripePaymentAmount(res.amount)
      } else {
        // Create play will create payment intent if payment is required
        const res = await bracketApi.createPlay(playReq)
        const newReq = {
          ...playReq,
          id: res.id,
        }
        playStorage.storePlay(newReq, bracket?.id)
        setStoredPlay(newReq)
        if (paymentRequired) {
          setStripeClientSecret(res.stripePaymentIntentClientSecret)
          setStripePaymentAmount(res.stripePaymentAmount)
        } else {
          window.location.assign(myPlayHistoryUrl)
          return
        }
      }
    } catch (err) {
      setSubmitPicksError(true)
      logger.error(err)
    }
    // At this point payment is required and process is finished
    setShowPaymentModal(true)
    setProcessingSubmitPicks(false)
  }

  const playBuilderProps = {
    matchTree,
    setMatchTree:
      canPrint || canSubmit ? setMatchTreeAndSaveInStorage : undefined,
    handleApparelClick: canPrint ? handleApparelClick : undefined,
    handleSubmitPicksClick: canSubmit ? handleSubmitPicksClick : undefined,
    canPlay: canPrint || canSubmit,
    processingAddToApparel,
    processingSubmitPicks,
    addToApparelError,
    submitPicksError,
    bracketMeta,
    setBracketMeta,
  }

  return (
    <MatchTreeContext.Provider
      value={{
        matchTree: matchTree,
        setMatchTree: setMatchTreeAndSaveInStorage,
      }}
    >
      <SubmitPicksRegisterModal
        show={showRegisterModal}
        setShow={setShowRegisterModal}
        signInUrl={props.loginUrl + '?redirect_to=' + bracket?.url}
        registerUrl={props.loginUrl + '?action=register'}
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
    </MatchTreeContext.Provider>
  )
}

const WrappedPlayBuilderPage = WithBracketMeta(
  WithWindowDimensions(PlayBuilderPage)
)

export default WrappedPlayBuilderPage
