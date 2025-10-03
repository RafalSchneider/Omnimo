import React, { useState, useEffect, useCallback } from "react";
import { useParams, Link, useNavigate } from "react-router-dom";
import { postsService, commentsService } from "../services/api";
import { useAuth } from "../contexts/useAuth";

const PostDetail = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { user, isAuthenticated } = useAuth();
  const [post, setPost] = useState(null);
  const [comments, setComments] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [commentText, setCommentText] = useState("");
  const [submittingComment, setSubmittingComment] = useState(false);

  const fetchPost = useCallback(async () => {
    try {
      setLoading(true);
      const response = await postsService.getPost(id);
      setPost(response.data);
      setComments(response.data.comments || []);
      setError("");
    } catch (error) {
      setError("Failed to fetch post. Please try again.");
      console.error("Error fetching post:", error);
    } finally {
      setLoading(false);
    }
  }, [id]);

  useEffect(() => {
    fetchPost();
  }, [fetchPost]);

  const handleDeletePost = async () => {
    if (window.confirm("Are you sure you want to delete this post?")) {
      try {
        await postsService.deletePost(id);
        navigate("/");
      } catch {
        setError("Failed to delete post. Please try again.");
      }
    }
  };

  const handleSubmitComment = async (e) => {
    e.preventDefault();
    if (!commentText.trim()) return;

    try {
      setSubmittingComment(true);
      const response = await commentsService.addComment(id, {
        comment: commentText,
      });
      setComments([response.data, ...comments]);
      setCommentText("");
    } catch {
      setError("Failed to add comment. Please try again.");
    } finally {
      setSubmittingComment(false);
    }
  };

  const handleDeleteComment = async (commentId) => {
    if (window.confirm("Are you sure you want to delete this comment?")) {
      try {
        await commentsService.deleteComment(commentId);
        setComments(comments.filter((comment) => comment.id !== commentId));
      } catch {
        setError("Failed to delete comment. Please try again.");
      }
    }
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString("en-US", {
      year: "numeric",
      month: "long",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  };

  if (loading) {
    return (
      <div className="container-fluid px-4 mt-4">
        <div className="text-center">
          <div className="spinner-border" role="status">
            <span className="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
    );
  }

  if (!post) {
    return (
      <div className="container-fluid px-4 mt-4">
        <div className="alert alert-danger" role="alert">
          Post not found.
        </div>
        <Link to="/" className="btn btn-primary">
          Back to Posts
        </Link>
      </div>
    );
  }

  return (
    <div className="container-fluid px-4 mt-4">
      <nav aria-label="breadcrumb">
        <ol className="breadcrumb">
          <li className="breadcrumb-item">
            <Link to="/">Home</Link>
          </li>
          <li className="breadcrumb-item active" aria-current="page">
            {post.title}
          </li>
        </ol>
      </nav>

      {error && (
        <div className="alert alert-danger" role="alert">
          {error}
        </div>
      )}

      <div className="card">
        <div className="card-header d-flex justify-content-between align-items-start">
          <div>
            <h1 className="h3 mb-1">{post.title}</h1>
            <p className="text-muted mb-0">
              By {post.user?.name} on {formatDate(post.created_at)}
            </p>
          </div>
          {user && user.id === post.user_id && (
            <div className="btn-group">
              <Link
                to={`/posts/${post.id}/edit`}
                className="btn btn-sm btn-outline-primary"
              >
                Edit
              </Link>
              <button
                className="btn btn-sm btn-outline-danger"
                onClick={handleDeletePost}
              >
                Delete
              </button>
            </div>
          )}
        </div>
        <div className="card-body">
          <div style={{ whiteSpace: "pre-wrap" }}>{post.content}</div>
        </div>
      </div>

      <div className="mt-5">
        <h4>Comments ({comments.length})</h4>

        {isAuthenticated ? (
          <form onSubmit={handleSubmitComment} className="mb-4">
            <div className="mb-3">
              <label htmlFor="comment" className="form-label">
                Add a comment
              </label>
              <textarea
                className="form-control"
                id="comment"
                rows="3"
                value={commentText}
                onChange={(e) => setCommentText(e.target.value)}
                placeholder="Write your comment here..."
                required
              ></textarea>
            </div>
            <button
              type="submit"
              className="btn btn-primary"
              disabled={submittingComment || !commentText.trim()}
            >
              {submittingComment ? "Adding..." : "Add Comment"}
            </button>
          </form>
        ) : (
          <p className="text-muted mb-4">
            <Link to="/login">Login</Link> to add a comment.
          </p>
        )}

        {comments.length === 0 ? (
          <p className="text-muted">
            No comments yet. Be the first to comment!
          </p>
        ) : (
          <div className="space-y-4">
            {comments.map((comment) => (
              <div key={comment.id} className="card mb-3">
                <div className="card-body">
                  <div className="d-flex justify-content-between align-items-start mb-2">
                    <h6 className="card-subtitle text-muted">
                      {comment.user?.name} • {formatDate(comment.created_at)}
                    </h6>
                    {user &&
                      (user.id === comment.user_id ||
                        user.id === post.user_id) && (
                        <button
                          className="btn btn-sm btn-outline-danger"
                          onClick={() => handleDeleteComment(comment.id)}
                        >
                          Delete
                        </button>
                      )}
                  </div>
                  <p className="card-text" style={{ whiteSpace: "pre-wrap" }}>
                    {comment.comment}
                  </p>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
};

export default PostDetail;
