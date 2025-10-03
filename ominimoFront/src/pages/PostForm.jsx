import React, { useState, useEffect, useCallback } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { postsService } from "../services/api";
import { useAuth } from "../contexts/useAuth";

const PostForm = () => {
  const navigate = useNavigate();
  const { id } = useParams();
  const { isAuthenticated } = useAuth();
  const isEditing = Boolean(id);

  const [formData, setFormData] = useState({
    title: "",
    content: "",
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [fetchingPost, setFetchingPost] = useState(isEditing);

  const fetchPost = useCallback(async () => {
    try {
      setFetchingPost(true);
      const response = await postsService.getPost(id);
      setFormData({
        title: response.data.title,
        content: response.data.content,
      });
      setError("");
    } catch (error) {
      setError("Failed to fetch post. Please try again.");
      console.error("Error fetching post:", error);
    } finally {
      setFetchingPost(false);
    }
  }, [id]);

  useEffect(() => {
    if (!isAuthenticated) {
      navigate("/login");
      return;
    }

    if (isEditing) {
      fetchPost();
    }
  }, [isAuthenticated, isEditing, navigate, fetchPost]);

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError("");
    setLoading(true);

    try {
      if (isEditing) {
        await postsService.updatePost(id, formData);
      } else {
        await postsService.createPost(formData);
      }
      navigate("/");
    } catch (err) {
      setError(
        err.response?.data?.message || "Failed to save post. Please try again."
      );
    } finally {
      setLoading(false);
    }
  };

  if (fetchingPost) {
    return (
      <div className="container-fluid mt-4">
        <div className="text-center">
          <div className="spinner-border" role="status">
            <span className="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="container-fluid mt-4">
      <div className="row justify-content-center">
        <div className="col-lg-8 col-xl-6">
          <div className="card">
            <div className="card-header">
              <h4 className="mb-0">
                {isEditing ? "Edit Post" : "Create New Post"}
              </h4>
            </div>
            <div className="card-body">
              {error && (
                <div className="alert alert-danger" role="alert">
                  {error}
                </div>
              )}

              <form onSubmit={handleSubmit}>
                <div className="mb-3">
                  <label htmlFor="title" className="form-label">
                    Title *
                  </label>
                  <input
                    type="text"
                    className="form-control"
                    id="title"
                    name="title"
                    value={formData.title}
                    onChange={handleChange}
                    placeholder="Enter post title"
                    required
                  />
                </div>

                <div className="mb-3">
                  <label htmlFor="content" className="form-label">
                    Content *
                  </label>
                  <textarea
                    className="form-control"
                    id="content"
                    name="content"
                    rows="10"
                    value={formData.content}
                    onChange={handleChange}
                    placeholder="Write your post content here..."
                    required
                  ></textarea>
                </div>

                <div className="d-flex gap-2">
                  <button
                    type="submit"
                    className="btn btn-primary"
                    disabled={loading}
                  >
                    {loading
                      ? isEditing
                        ? "Updating..."
                        : "Creating..."
                      : isEditing
                      ? "Update Post"
                      : "Create Post"}
                  </button>
                  <button
                    type="button"
                    className="btn btn-secondary"
                    onClick={() => navigate(-1)}
                  >
                    Cancel
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default PostForm;
